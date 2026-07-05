<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delivery_worker_id' => ['nullable', 'integer', 'exists:delivery_workers,id'],
            'status'             => ['nullable', 'string', 'in:pending,accepted,picked_up,in_transit,delivered,failed,rejected'],
            'latitude'           => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'          => ['nullable', 'numeric', 'between:-180,180'],
            'address'            => ['nullable', 'string', 'max:255'],
        ];
    }
}
