<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramRecipient extends Model
{
    protected $fillable = ['user_id', 'chat_id', 'name', 'enabled', 'verified_at'];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }
}
