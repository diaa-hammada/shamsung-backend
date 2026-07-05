<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Delivery;

use Illuminate\Foundation\Http\FormRequest;

class AcceptDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estimated_time' => ['required', 'date'],
        ];
    }
}
