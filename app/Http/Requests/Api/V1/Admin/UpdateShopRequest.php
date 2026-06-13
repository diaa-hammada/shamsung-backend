<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'required', 'string', 'max:255'],
            'address'   => ['sometimes', 'required', 'string', 'max:255'],
            'phone'     => ['sometimes', 'required', 'string', 'max:20'],
            'latitude'  => ['sometimes', 'required', 'numeric'],
            'longitude' => ['sometimes', 'required', 'numeric'],
            'image'     => ['nullable', 'image', 'max:5120'],
        ];
    }
}
