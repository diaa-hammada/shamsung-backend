<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccessoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_id'        => ['required', 'integer', 'exists:shops,id'],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_active'      => ['boolean'],
            'image'          => ['nullable', 'image', 'max:5120'],
        ];
    }
}
