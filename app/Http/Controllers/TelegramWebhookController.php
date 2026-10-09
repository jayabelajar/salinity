<?php

namespace App\Http\Controllers;

use App\Services\TelegramCommandService;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function __invoke(Request $request, TelegramCommandService $commandService, TelegramService $telegramService): JsonResponse
    {
        $secret = config('services.telegram.webhook_secret');

        if ($secret && $request->header('X-Telegram-Bot-Api-Secret-Token') !== $secret) {
            return response()->json(['message' => 'Invalid webhook secret.'], 401);
        }

        $message = $request->input('message', []);
        $chatId = (string) data_get($message, 'chat.id');
        $text = (string) data_get($message, 'text', '/help');
        $name = trim((string) data_get($message, 'from.first_name').' '.(string) data_get($message, 'from.last_name'));

        if ($chatId !== '') {
            $reply = $commandService->handle($chatId, $text, $name);
            $telegramService->sendMessage($chatId, $reply);
        }

        return response()->json(['ok' => true]);
    }
}
