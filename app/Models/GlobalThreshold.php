<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GlobalThreshold extends Model
{
    protected $fillable = [
        'parameter_slug',
        'min_value',
        'max_value',
        'ignore_max',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_value' => 'decimal:6',
            'max_value' => 'decimal:6',
            'ignore_max' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
