<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\TelegramPairingCode;
use App\Models\TelegramRecipient;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    public function recipients(): JsonResponse
    {
        return response()->json(TelegramRecipient::query()->latest()->get());
    }

    public function pairing(Request $request): JsonResponse
    {
        $user = $request->user() ?? User::query()->firstOrFail();
        $code = Str::upper(Str::random(8));

        TelegramPairingCode::query()->create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        return response()->json([
            'pairing_code' => $code,
            'expires_at' => now()->addMinutes(15)->toISOString(),
        ]);
    }

    public function test(Request $request, TelegramService $telegramService): JsonResponse
    {
        $data = $request->validate([
            'chat_id' => ['nullable', 'string'],
            'message' => ['nullable', 'string'],
        ]);

        $recipient = isset($data['chat_id'])
            ? TelegramRecipient::query()->where('chat_id', $data['chat_id'])->first()
            : TelegramRecipient::query()->where('enabled', true)->first();

        if (! $recipient) {
            return response()->json(['message' => 'Belum ada recipient Telegram aktif.'], 422);
        }

        $telegramService->sendMessage($recipient->chat_id, $data['message'] ?? 'Test notifikasi SalinityGuard berhasil.');

        return response()->json(['message' => 'Test notification processed.']);
    }

    public function notifications(): JsonResponse
    {
        return response()->json(NotificationLog::query()->latest()->paginate(30));
    }
}
