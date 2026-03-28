<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alert extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    protected $fillable = [
        'dgu_id',
        'parameter_slug',
        'status',
        'title',
        'message',
        'triggered_value',
        'triggered_at',
        'acknowledged_at',
        'acknowledged_by_id',
        'acknowledge_comment',
    ];

    protected function casts(): array
    {
        return [
            'triggered_at' => 'datetime',
            'acknowledged_at' => 'datetime',
        ];
    }

    public function dgu(): BelongsTo
    {
        return $this->belongsTo(Dgu::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(AlertEvent::class)->orderBy('created_at');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }
}
