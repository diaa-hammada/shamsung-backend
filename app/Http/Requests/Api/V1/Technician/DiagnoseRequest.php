<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Technician;

use Illuminate\Foundation\Http\FormRequest;

class DiagnoseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'estimated_days'           => ['required', 'integer', 'min:1'],
            'parts'                    => ['required', 'array', 'min:1'],
            'parts.*.spare_part_id'    => ['required', 'integer', 'exists:spare_parts,id'],
            'parts.*.quantity'         => ['required', 'integer', 'min:1'],
            'parts.*.is_required'      => ['required', 'boolean'],
        ];
    }
}
