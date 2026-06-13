<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockRequest extends Model
{
    protected $fillable = ['shop_id', 'spare_part_id', 'quantity', 'status'];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}