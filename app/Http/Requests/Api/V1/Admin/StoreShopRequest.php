<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'address'   => ['required', 'string', 'max:255'],
            'phone'     => ['required', 'string', 'max:20'],
            'latitude'  => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
            'image'     => ['nullable', 'image', 'max:5120'],
        ];
    }
}
