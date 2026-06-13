<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $fillable = [
        'phone',
        'code',
        'is_used',
        'phone_verified',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'is_used'        => 'boolean',
            'phone_verified' => 'boolean',
            'expires_at'     => 'datetime',
        ];
    }
}
