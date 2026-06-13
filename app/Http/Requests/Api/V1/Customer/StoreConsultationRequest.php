<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'consultation_type' => ['required', 'string', 'in:technician,ai'],
            'message'           => ['nullable', 'string'],
            'image'             => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (empty($this->input('message')) && ! $this->hasFile('image')) {
                $validator->errors()->add('message', 'Message or image is required.');
            }
        });
    }
}
