<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Delivery;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'confirmation_code' => ['nullable', 'string', 'max:255'],
            'image'             => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->filled('confirmation_code') && !$this->hasFile('image')) {
                $validator->errors()->add('confirmation_code', 'Either a confirmation code or an image is required.');
            }
        });
    }
}
