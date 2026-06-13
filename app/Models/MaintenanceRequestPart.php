<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequestPart extends Model
{
    protected $fillable = [
        'maintenance_request_id',
        'spare_part_id',
        'name',
        'price',
        'quantity',
        'unit_price',
        'is_required',
        'is_selected',
    ];

    protected function casts(): array
    {
        return [
            'unit_price'  => 'float',
            'price'       => 'float',
            'quantity'    => 'integer',
            'is_required' => 'boolean',
            'is_selected' => 'boolean',
        ];
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}