<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/devices', 'dashboard')->name('devices');
    Route::view('/devices/{device}', 'dashboard')->name('devices.show');
    Route::view('/monitoring', 'dashboard')->name('monitoring');
    Route::view('/readings', 'dashboard')->name('readings');
    Route::view('/alerts', 'dashboard')->name('alerts');
    Route::view('/synchronization', 'dashboard')->name('synchronization');
    Route::view('/analytics', 'dashboard')->name('analytics');
    Route::view('/reports', 'dashboard')->name('reports');
    Route::view('/settings', 'dashboard')->name('settings');
    Route::view('/settings/users', 'dashboard')->name('settings.users');
    Route::view('/settings/telegram', 'dashboard')->name('settings.telegram');
    Route::view('/profile', 'dashboard')->name('profile');
});
