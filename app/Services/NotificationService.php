<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\NotificationLog;
use App\Models\TelegramRecipient;
use Throwable;

class NotificationService
{
    public function __construct(private TelegramService $telegramService) {}

    public function notifyAlert(Alert $alert): void
    {
        $alert->loadMissing(['device', 'reading']);
        $type = $alert->risk_level === 'BAHAYA' ? 'danger' : 'warning';
        $message = $this->formatAlert($alert);

        TelegramRecipient::query()
            ->where('enabled', true)
            ->each(function (TelegramRecipient $recipient) use ($alert, $message, $type): void {
                $eventKey = "{$type}:{$alert->id}:{$recipient->id}";
                $log = NotificationLog::query()->firstOrCreate(
                    ['recipient_id' => $recipient->id, 'event_key' => $eventKey],
                    [
                        'alert_id' => $alert->id,
                        'notification_type' => 'telegram',
                        'message' => $message,
                        'status' => 'queued',
                    ],
                );

                if (! $log->wasRecentlyCreated) {
                    return;
                }

                try {
                    $response = $this->telegramService->sendMessage($recipient->chat_id, $message);
                    $log->update([
                        'status' => $response === null || $response->successful() ? 'sent' : 'failed',
                        'attempts' => 1,
                        'sent_at' => now(),
                        'error_message' => $response?->failed() ? $response->body() : null,
                    ]);
                } catch (Throwable $throwable) {
                    $log->update([
                        'status' => 'failed',
                        'attempts' => 1,
                        'error_message' => $throwable->getMessage(),
                    ]);
                }
            });
    }

    private function formatAlert(Alert $alert): string
    {
        $heading = $alert->risk_level === 'BAHAYA'
            ? 'PERINGATAN SALINITAS - BAHAYA'
            : 'PERINGATAN SALINITAS';

        return implode("\n", [
            "<b>{$heading}</b>",
            "Device: {$alert->device->device_uid}",
            "EC: {$alert->reading?->ec_corrected} mS/cm",
            "Temperature: {$alert->reading?->temperature} C",
            "Fuzzy Score: {$alert->reading?->fuzzy_score}",
            "Time: {$alert->occurred_at?->toDateTimeString()}",
            '',
            'Recommendation: Periksa kualitas air irigasi.',
        ]);
    }
}
