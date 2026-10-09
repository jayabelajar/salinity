<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectionLog extends Model
{
    protected $fillable = ['device_id', 'status', 'occurred_at', 'received_at'];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'received_at' => 'datetime',
        ];
    }
}
