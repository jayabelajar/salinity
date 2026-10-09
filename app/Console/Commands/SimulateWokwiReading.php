<?php

namespace App\Console\Commands;

use App\Services\SensorIngestionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('salinity:simulate-wokwi {device=esp32-001} {--risk=NORMAL}')]
#[Description('Simulate a Wokwi ESP32 payload through the same ingestion service used by the API.')]
class SimulateWokwiReading extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SensorIngestionService $ingestionService): int
    {
        $risk = strtoupper((string) $this->option('risk'));
        $ec = match ($risk) {
            'BAHAYA' => fake()->randomFloat(2, 3.2, 4.8),
            'WASPADA' => fake()->randomFloat(2, 2.1, 3.1),
            default => fake()->randomFloat(2, 1.1, 2.0),
        };

        $payload = [
            'event_id' => $this->argument('device').'-'.now()->format('YmdHis'),
            'device_id' => $this->argument('device'),
            'measured_at' => now()->toISOString(),
            'ec_raw' => $ec,
            'temperature' => fake()->randomFloat(1, 26, 31),
            'ec_corrected' => $ec,
            'ec_rate' => fake()->randomFloat(2, -0.05, 0.2),
            'fuzzy_score' => match ($risk) {
                'BAHAYA' => fake()->randomFloat(1, 71, 95),
                'WASPADA' => fake()->randomFloat(1, 36, 70),
                default => fake()->randomFloat(1, 5, 35),
            },
            'risk_level' => $risk,
            'is_backfill' => false,
            'firmware_version' => 'wokwi-1.0.0',
        ];

        $result = $ingestionService->ingest($payload);

        $this->info(json_encode($result['ack'], JSON_PRETTY_PRINT));

        return self::SUCCESS;
    }
}
