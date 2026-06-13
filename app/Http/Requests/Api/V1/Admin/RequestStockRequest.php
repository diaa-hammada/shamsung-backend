<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RequestStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shop_id'       => ['required', 'integer', 'exists:shops,id'],
            'spare_part_id' => ['required', 'integer', 'exists:spare_parts,id'],
            'quantity'      => ['required', 'integer', 'min:1'],
        ];
    }
}
