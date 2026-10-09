<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id',
        'device_id',
        'warning_enabled',
        'danger_enabled',
        'offline_enabled',
        'recovery_enabled',
        'sync_enabled',
        'cooldown_minutes',
    ];

    protected function casts(): array
    {
        return [
            'warning_enabled' => 'boolean',
            'danger_enabled' => 'boolean',
            'offline_enabled' => 'boolean',
            'recovery_enabled' => 'boolean',
            'sync_enabled' => 'boolean',
        ];
    }
}
