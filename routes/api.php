<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\ReadingController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TelegramWebhookController;
use App\Models\SyncLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/iot/readings', [ReadingController::class, 'ingest']);
Route::post('/telegram/webhook', TelegramWebhookController::class);

Route::middleware(['web', 'auth'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

    Route::apiResource('devices', DeviceController::class);
    Route::get('/readings', [ReadingController::class, 'index']);
    Route::get('/readings/latest', [ReadingController::class, 'latest']);
    Route::get('/devices/{device}/readings', [ReadingController::class, 'byDevice']);

    Route::get('/alerts', [AlertController::class, 'index']);
    Route::get('/alerts/{alert}', [AlertController::class, 'show']);
    Route::patch('/alerts/{alert}/acknowledge', [AlertController::class, 'acknowledge']);
    Route::patch('/alerts/{alert}/resolve', [AlertController::class, 'resolve']);
    Route::post('/alerts/{alert}/notify', [AlertController::class, 'notify']);

    Route::get('/synchronization', [DashboardController::class, 'summary']);
    Route::get('/synchronization/logs', fn () => response()->json(SyncLog::query()->latest('received_at')->paginate(30)));

    Route::get('/analytics', [DashboardController::class, 'summary']);
    Route::get('/analytics/ec', [ReadingController::class, 'index']);
    Route::get('/analytics/risks', [DashboardController::class, 'summary']);
    Route::get('/reports', [DashboardController::class, 'summary']);
    Route::get('/reports/export', [ReadingController::class, 'index']);

    Route::get('/telegram/recipients', [TelegramController::class, 'recipients']);
    Route::post('/telegram/pairing', [TelegramController::class, 'pairing']);
    Route::post('/telegram/test', [TelegramController::class, 'test']);
    Route::get('/telegram/notifications', [TelegramController::class, 'notifications']);
    Route::get('/settings', fn () => response()->json(SystemSetting::query()->pluck('value', 'key')));
    Route::put('/settings', fn () => response()->json(['message' => 'Settings endpoint ready.']));
});
