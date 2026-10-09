<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TelegramService
{
    public function sendMessage(string $chatId, string $message): ?Response
    {
        $token = config('services.telegram.bot_token');

        if (! $token || ! config('services.telegram.notifications_enabled')) {
            return null;
        }

        return Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
        ]);
    }
}
