<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'                   => ['required', 'string', 'in:device_pickup,device_dropoff,accessory_delivery'],
            'customer_id'            => ['required', 'integer', 'exists:customers,id'],
            'shop_id'                => ['required', 'integer', 'exists:shops,id'],
            'latitude'               => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'              => ['nullable', 'numeric', 'between:-180,180'],
            'address'                => ['nullable', 'string', 'max:255'],
            'maintenance_request_id' => ['nullable', 'integer', 'exists:maintenance_requests,id'],
            'order_id'               => ['nullable', 'integer', 'exists:orders,id'],
            'delivery_worker_id'     => ['nullable', 'integer', 'exists:delivery_workers,id'],
            'payment_method'         => ['nullable', 'string', 'in:cash_on_delivery,prepaid'],
            'notes'                  => ['nullable', 'string'],
        ];
    }
}
