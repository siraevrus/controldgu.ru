<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Dgu extends Model
{
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    protected $fillable = [
        'public_id',
        'name',
        'serial_number',
        'address',
        'latitude',
        'longitude',
        'responsible_name',
        'contact_phone',
        'nominal_power_kw',
        'model_name',
        'region',
        'tags',
        'is_manually_disabled',
        'operational_state',
        'telemetry_token_hash',
        'last_telemetry_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_manually_disabled' => 'boolean',
            'last_telemetry_at' => 'datetime',
            'nominal_power_kw' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Dgu $dgu): void {
            $dgu->public_id ??= (string) Str::uuid();
        });
    }

    public static function hashTelemetryToken(string $plain): string
    {
        return hash('sha256', $plain);
    }

    public function telemetrySnapshots(): HasMany
    {
        return $this->hasMany(TelemetrySnapshot::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function verifiesTelemetryToken(?string $plain): bool
    {
        if ($plain === null || $plain === '') {
            return false;
        }

        return hash_equals($this->telemetry_token_hash, self::hashTelemetryToken($plain));
    }

    public function isTelemetryFresh(int $staleMinutes): bool
    {
        if ($this->last_telemetry_at === null) {
            return false;
        }

        return $this->last_telemetry_at->greaterThanOrEqualTo(now()->subMinutes($staleMinutes));
    }
}
