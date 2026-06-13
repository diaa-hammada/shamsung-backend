<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTechnicianRequest extends FormRequest
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
            'phone'          => ['sometimes', 'required', 'string', 'max:20', 'unique:technicians,phone,' . $id],
            'email'          => ['sometimes', 'nullable', 'email', 'unique:technicians,email,' . $id],
            'shop_id'        => ['sometimes', 'required', 'integer', 'exists:shops,id'],
            'specialization' => ['sometimes', 'required', 'string', 'max:255'],
            'experience'     => ['sometimes', 'required', 'string', 'max:255'],
            'birthdate'      => ['nullable', 'date'],
            'is_active'      => ['boolean'],
        ];
    }
}
