<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Otp;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'code'  => ['required', 'string', 'size:5'],
        ];
    }
}
