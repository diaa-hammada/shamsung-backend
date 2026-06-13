<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Customer;

use Illuminate\Foundation\Http\FormRequest;

class ApproveMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_parts'   => ['required', 'array'],
            'selected_parts.*' => ['required', 'integer'],
            'payment_method'   => ['required', 'string', 'in:cash_on_delivery,pay_after_service'],
        ];
    }
}
