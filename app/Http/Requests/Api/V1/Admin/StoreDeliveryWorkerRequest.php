<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name'     => ['required', 'string', 'max:255'],
            'last_name'      => ['required', 'string', 'max:255'],
            'phone'          => ['required', 'string', 'max:20', 'unique:delivery_workers,phone'],
            'email'          => ['nullable', 'email', 'unique:delivery_workers,email'],
            'shop_id'        => ['nullable', 'integer', 'exists:shops,id'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'experience'     => ['nullable', 'string', 'max:255'],
            'birthdate'      => ['nullable', 'date'],
            'is_active'      => ['boolean'],
        ];
    }
}
