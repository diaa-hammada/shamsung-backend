<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeliveryWorkerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'first_name'     => ['sometimes', 'required', 'string', 'max:255'],
            'last_name'      => ['sometimes', 'required', 'string', 'max:255'],
            'phone'          => ['sometimes', 'required', 'string', 'max:20', 'unique:delivery_workers,phone,' . $id],
            'email'          => ['sometimes', 'nullable', 'email', 'unique:delivery_workers,email,' . $id],
            'shop_id'        => ['sometimes', 'nullable', 'integer', 'exists:shops,id'],
            'specialization' => ['sometimes', 'nullable', 'string', 'max:255'],
            'experience'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'birthdate'      => ['nullable', 'date'],
            'is_active'      => ['boolean'],
        ];
    }
}
