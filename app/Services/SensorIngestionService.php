<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\ConnectionLog;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\SyncLog;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class SensorIngestionService
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array{device: Device, reading: SensorReading, duplicate: bool, ack: array<string, mixed>}
     */
    public function ingest(array $payload): array
    {
        return DB::transaction(function () use ($payload): array {
            $receivedAt = now();
            $deviceUid = (string) ($payload['device_id'] ?? $payload['device_uid'] ?? 'esp32-001');
            $measuredAt = CarbonImmutable::parse($payload['measured_at'] ?? $receivedAt);

            $device = Device::query()->firstOrCreate(
                ['device_uid' => $deviceUid],
                [
                    'name' => strtoupper($deviceUid),
                    'location' => 'Wokwi Simulator',
                    'description' => 'Auto-registered from IoT ingest endpoint.',
                ],
            );

            $previousStatus = $device->status;
            $device->update([
                'status' => 'online',
                'last_seen_at' => $receivedAt,
                'firmware_version' => $payload['firmware_version'] ?? $device->firmware_version,
            ]);

            if ($previousStatus !== 'online') {
                ConnectionLog::query()->create([
                    'device_id' => $device->id,
                    'status' => 'online',
                    'occurred_at' => $receivedAt,
                    'received_at' => $receivedAt,
                ]);
            }

            $duplicate = SensorReading::query()
                ->where('device_id', $device->id)
                ->where('event_id', (string) $payload['event_id'])
                ->exists();

            $reading = SensorReading::query()->firstOrCreate(
                [
                    'device_id' => $device->id,
                    'event_id' => (string) $payload['event_id'],
                ],
                [
                    'ec_raw' => $payload['ec_raw'],
                    'temperature' => $payload['temperature'],
                    'ec_corrected' => $payload['ec_corrected'] ?? $payload['ec_raw'],
                    'ec_rate' => $payload['ec_rate'] ?? 0,
                    'fuzzy_score' => $payload['fuzzy_score'],
                    'risk_level' => strtoupper((string) $payload['risk_level']),
                    'measured_at' => $measuredAt,
                    'received_at' => $receivedAt,
                    'is_backfill' => (bool) ($payload['is_backfill'] ?? false),
                ],
            );

            SyncLog::query()->create([
                'device_id' => $device->id,
                'event_id' => (string) $payload['event_id'],
                'sync_delay_ms' => max(0, (int) $measuredAt->diffInMilliseconds($receivedAt)),
                'duplicate_detected' => $duplicate,
                'received_at' => $receivedAt,
            ]);

            if (! $duplicate) {
                $this->evaluateAlert($device, $reading);
            }

            return [
                'device' => $device,
                'reading' => $reading,
                'duplicate' => $duplicate,
                'ack' => [
                    'acknowledged' => true,
                    'event_id' => $payload['event_id'],
                    'device_id' => $deviceUid,
                    'duplicate' => $duplicate,
                    'received_at' => $receivedAt->toISOString(),
                ],
            ];
        });
    }

    private function evaluateAlert(Device $device, SensorReading $reading): void
    {
        if ($reading->risk_level === 'NORMAL') {
            Alert::query()
                ->where('device_id', $device->id)
                ->whereIn('status', ['NEW', 'ACKNOWLEDGED'])
                ->update([
                    'status' => 'RESOLVED',
                    'resolved_at' => now(),
                    'notes' => 'Auto-resolved after sensor risk returned to NORMAL.',
                ]);

            return;
        }

        $openAlert = Alert::query()
            ->where('device_id', $device->id)
            ->whereIn('status', ['NEW', 'ACKNOWLEDGED'])
            ->latest('occurred_at')
            ->first();

        if ($openAlert !== null) {
            $openAlert->update([
                'reading_id' => $reading->id,
                'risk_level' => $reading->risk_level,
                'occurred_at' => $reading->measured_at,
            ]);

            return;
        }

        Alert::query()->create([
            'device_id' => $device->id,
            'reading_id' => $reading->id,
            'risk_level' => $reading->risk_level,
            'status' => 'NEW',
            'occurred_at' => $reading->measured_at,
        ]);
    }
}
