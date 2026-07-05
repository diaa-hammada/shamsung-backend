<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Technician extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'shop_id',
        'first_name',
        'last_name',
        'birthdate',
        'email',
        'phone',
        'password',
        'specialization',
        'experience',
        'is_active',
        'fcm_token',
        'last_seen_at',
    ];

    protected $appends = [
        'is_online',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'birthdate'    => 'date',
            'is_active'    => 'boolean',
            'password'     => 'hashed',
            'last_seen_at' => 'datetime',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function isOnline(): Attribute
    {
        return Attribute::get(
            fn () => $this->last_seen_at !== null
                && $this->last_seen_at->gt(now()->subMinutes(5))
        );
    }
}