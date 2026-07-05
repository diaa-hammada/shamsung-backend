<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class MaintenanceRequest extends Model
{
    use HasFactory;

    public const ACTIVE_STATUSES = ['pending', 'under_inspection', 'waiting_customer_approval', 'approved'];

    protected $fillable = [
        'tracking_number',
        'customer_id',
        'shop_id',
        'device_model',
        'problem_description',
        'status',
        'customer_status',
        'rejection_reason',
        'payment_method',
        'estimated_cost',
        'estimated_days',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->tracking_number = 'SHM-' . strtoupper(Str::random(8));
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function parts()
    {
        return $this->hasMany(MaintenanceRequestPart::class);
    }
}