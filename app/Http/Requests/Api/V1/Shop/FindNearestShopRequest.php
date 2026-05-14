<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Shop;

use Illuminate\Foundation\Http\FormRequest;

class FindNearestShopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}