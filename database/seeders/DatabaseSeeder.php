<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\SyncLog;
use App\Models\TelegramRecipient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@salinityguard.local'],
            [
                'name' => 'AgriTech Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        $devices = collect([
            ['device_uid' => 'esp32-001', 'name' => 'Node Alpha 01', 'location' => 'Plot A - Intake', 'status' => 'online'],
            ['device_uid' => 'esp32-002', 'name' => 'Node Beta 02', 'location' => 'Plot B - Canal', 'status' => 'online'],
            ['device_uid' => 'esp32-003', 'name' => 'Node Gamma 03', 'location' => 'Plot C - Pump', 'status' => 'offline'],
        ])->map(fn (array $device): Device => Device::query()->updateOrCreate(
            ['device_uid' => $device['device_uid']],
            $device + [
                'description' => 'Demo field node for Wokwi simulation.',
                'last_seen_at' => now()->subMinutes($device['status'] === 'offline' ? 42 : rand(1, 7)),
                'firmware_version' => '1.0.0',
            ],
        ));

        foreach ($devices as $index => $device) {
            for ($i = 11; $i >= 0; $i--) {
                $ec = round(1.5 + ($index * 0.45) + (sin($i) * 0.25), 2);
                $risk = $ec > 2.75 ? 'BAHAYA' : ($ec > 2.1 ? 'WASPADA' : 'NORMAL');
                $score = $risk === 'BAHAYA' ? 78 + $index : ($risk === 'WASPADA' ? 48 + $index : 24 + $index);
                $reading = SensorReading::query()->updateOrCreate(
                    ['device_id' => $device->id, 'event_id' => "{$device->device_uid}-seed-{$i}"],
                    [
                        'ec_raw' => $ec,
                        'temperature' => round(27 + ($index * 0.7) + (cos($i) * 0.3), 1),
                        'ec_corrected' => $ec,
                        'ec_rate' => round(0.02 * $index, 2),
                        'fuzzy_score' => $score,
                        'risk_level' => $risk,
                        'measured_at' => now()->subMinutes($i * 10),
                        'received_at' => now()->subMinutes($i * 10)->addSeconds(3),
                        'is_backfill' => false,
                    ],
                );

                SyncLog::query()->firstOrCreate(
                    ['device_id' => $device->id, 'event_id' => $reading->event_id],
                    ['sync_delay_ms' => 3000, 'duplicate_detected' => false, 'received_at' => $reading->received_at],
                );
            }
        }

        $dangerReading = SensorReading::query()->where('risk_level', 'BAHAYA')->latest('measured_at')->first();

        if ($dangerReading) {
            Alert::query()->updateOrCreate(
                ['device_id' => $dangerReading->device_id, 'reading_id' => $dangerReading->id],
                ['risk_level' => 'BAHAYA', 'status' => 'NEW', 'occurred_at' => $dangerReading->measured_at],
            );
        }

        TelegramRecipient::query()->updateOrCreate(
            ['chat_id' => '123456789'],
            ['user_id' => $admin->id, 'name' => 'Demo Telegram User', 'enabled' => true, 'verified_at' => now()],
        );
    }
}
