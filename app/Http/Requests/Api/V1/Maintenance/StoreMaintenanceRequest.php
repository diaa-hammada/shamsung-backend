<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Maintenance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_id' => ['required', 'integer', Rule::exists('shops', 'id')->where('is_active', true)],
            'device_model' => ['required', 'string', 'max:255'],
            'problem_description' => ['required', 'string', 'max:1000'],
        ];
    }
}