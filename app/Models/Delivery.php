<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = [
        'type',
        'payment_method',
        'delivery_worker_id',
        'customer_id',
        'shop_id',
        'latitude',
        'longitude',
        'address',
        'maintenance_request_id',
        'order_id',
        'status',
        'notes',
        'estimated_time',
        'confirmation_code',
        'confirmation_image_path',
        'confirmed_at',
        'cash_collected',
        'cash_amount',
    ];

    protected function casts(): array
    {
        return [
            'estimated_time' => 'datetime',
            'confirmed_at'   => 'datetime',
            'cash_collected' => 'boolean',
            'cash_amount'    => 'decimal:2',
        ];
    }

    public function deliveryWorker(): BelongsTo
    {
        return $this->belongsTo(DeliveryWorker::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function maintenanceRequest(): BelongsTo
    {
        return $this->belongsTo(MaintenanceRequest::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}
