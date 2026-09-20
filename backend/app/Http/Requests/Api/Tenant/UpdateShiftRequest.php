<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;

class UpdateShiftRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'       => ['sometimes', 'string', 'max:100'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time'   => ['sometimes', 'date_format:H:i'],
            'is_active'  => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.date_format' => 'Start time must be in HH:MM format (e.g., 07:00)',
            'end_time.date_format'   => 'End time must be in HH:MM format (e.g., 15:00)',
        ];
    }
}
