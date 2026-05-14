<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accessory_id' => ['required', 'integer', 'exists:accessories,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}