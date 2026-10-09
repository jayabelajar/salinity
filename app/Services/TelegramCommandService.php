<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Device;
use App\Models\SensorReading;
use App\Models\TelegramRecipient;

class TelegramCommandService
{
    public function handle(string $chatId, string $text, ?string $name = null): string
    {
        $parts = preg_split('/\s+/', trim($text)) ?: [];
        $command = strtolower($parts[0] ?? '/help');
        $argument = $parts[1] ?? null;

        return match ($command) {
            '/start' => $this->handleStart($chatId, $name),
            '/devices' => $this->devices(),
            '/status' => $this->status($argument),
            '/sensor' => $this->sensor($argument),
            '/alerts' => $this->alerts(),
            '/history' => $this->history(),
            '/subscribe' => $this->subscription($chatId, true),
            '/unsubscribe' => $this->subscription($chatId, false),
            '/dashboard' => url('/dashboard'),
            default => $this->help(),
        };
    }

    private function handleStart(string $chatId, ?string $name): string
    {
        TelegramRecipient::query()->firstOrCreate(
            ['chat_id' => $chatId],
            ['name' => $name ?: "Telegram {$chatId}", 'enabled' => true, 'verified_at' => now()],
        );

        return "SalinityGuard Alert Bot aktif.\nKetik /help untuk daftar command.";
    }

    private function help(): string
    {
        return "/status [device_id]\n/sensor [device_id]\n/devices\n/alerts\n/history\n/subscribe\n/unsubscribe\n/dashboard";
    }

    private function devices(): string
    {
        return Device::query()->latest('last_seen_at')->get()
            ->map(fn (Device $device): string => "{$device->device_uid} - {$device->status} - {$device->location}")
            ->implode("\n") ?: 'Belum ada perangkat.';
    }

    private function status(?string $deviceUid): string
    {
        $query = Device::query()->latest('last_seen_at');
        $device = $deviceUid ? $query->where('device_uid', $deviceUid)->first() : $query->first();

        if (! $device) {
            return 'Device tidak ditemukan.';
        }

        return "Device: {$device->device_uid}\nStatus: {$device->status}\nLast seen: {$device->last_seen_at}\nFirmware: {$device->firmware_version}";
    }

    private function sensor(?string $deviceUid): string
    {
        $reading = SensorReading::query()
            ->with('device')
            ->when($deviceUid, fn ($query) => $query->whereHas('device', fn ($deviceQuery) => $deviceQuery->where('device_uid', $deviceUid)))
            ->latest('measured_at')
            ->first();

        if (! $reading) {
            return 'Data sensor belum tersedia.';
        }

        return "Device: {$reading->device->device_uid}\nEC: {$reading->ec_corrected} mS/cm\nTemp: {$reading->temperature} C\nRisk: {$reading->risk_level} ({$reading->fuzzy_score})\nTime: {$reading->measured_at}";
    }

    private function alerts(): string
    {
        return Alert::query()->with('device')->whereIn('status', ['NEW', 'ACKNOWLEDGED'])->latest('occurred_at')->limit(5)->get()
            ->map(fn (Alert $alert): string => "{$alert->risk_level} - {$alert->device->device_uid} - {$alert->status}")
            ->implode("\n") ?: 'Tidak ada alert aktif.';
    }

    private function history(): string
    {
        return Alert::query()->with('device')->latest('occurred_at')->limit(5)->get()
            ->map(fn (Alert $alert): string => "{$alert->occurred_at?->format('Y-m-d H:i')} - {$alert->device->device_uid} - {$alert->risk_level}")
            ->implode("\n") ?: 'History alert kosong.';
    }

    private function subscription(string $chatId, bool $enabled): string
    {
        $recipient = TelegramRecipient::query()->where('chat_id', $chatId)->first();

        if (! $recipient) {
            return 'Jalankan /start dulu untuk mendaftarkan chat ini.';
        }

        $recipient->update(['enabled' => $enabled]);

        return $enabled ? 'Notifikasi diaktifkan.' : 'Notifikasi dinonaktifkan.';
    }
}
