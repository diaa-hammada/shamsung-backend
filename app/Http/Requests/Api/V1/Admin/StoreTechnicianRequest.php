<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTechnicianRequest extends FormRequest
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
            'phone'          => ['required', 'string', 'max:20', 'unique:technicians,phone'],
            'email'          => ['nullable', 'email', 'unique:technicians,email'],
            'shop_id'        => ['required', 'integer', 'exists:shops,id'],
            'specialization' => ['required', 'string', 'max:255'],
            'experience'     => ['required', 'string', 'max:255'],
            'birthdate'      => ['nullable', 'date'],
            'is_active'      => ['boolean'],
        ];
    }
}
