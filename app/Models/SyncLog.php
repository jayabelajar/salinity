<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyncLog extends Model
{
    protected $fillable = ['device_id', 'event_id', 'sync_delay_ms', 'duplicate_detected', 'received_at'];

    protected function casts(): array
    {
        return [
            'duplicate_detected' => 'boolean',
            'received_at' => 'datetime',
        ];
    }
}
