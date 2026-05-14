<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['customer_id', 'accessory_id', 'quantity'];

    public function accessory(): BelongsTo
    {
        return $this->belongsTo(Accessory::class);
    }
}