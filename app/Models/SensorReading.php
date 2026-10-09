<?php

namespace App\Models;

use Database\Factories\SensorReadingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SensorReading extends Model
{
    /** @use HasFactory<SensorReadingFactory> */
    use HasFactory;

    protected $fillable = [
        'device_id',
        'event_id',
        'ec_raw',
        'temperature',
        'ec_corrected',
        'ec_rate',
        'fuzzy_score',
        'risk_level',
        'measured_at',
        'received_at',
        'is_backfill',
    ];

    protected function casts(): array
    {
        return [
            'ec_raw' => 'float',
            'temperature' => 'float',
            'ec_corrected' => 'float',
            'ec_rate' => 'float',
            'fuzzy_score' => 'float',
            'measured_at' => 'datetime',
            'received_at' => 'datetime',
            'is_backfill' => 'boolean',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function alert(): HasOne
    {
        return $this->hasOne(Alert::class, 'reading_id');
    }
}
