<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Accessory extends Model
{
    use HasFactory;

    protected $fillable = [
        'shop_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'image_url',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'stock_quantity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
}