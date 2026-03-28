<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelemetrySnapshot extends Model
{
    protected $fillable = ['dgu_id', 'recorded_at', 'values'];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'values' => 'array',
        ];
    }

    public function dgu(): BelongsTo
    {
        return $this->belongsTo(Dgu::class);
    }
}
