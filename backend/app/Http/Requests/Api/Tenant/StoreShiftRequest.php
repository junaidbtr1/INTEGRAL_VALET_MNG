<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;

class StoreShiftRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:100'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i'],
            'is_active'  => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'       => 'Shift name is required',
            'start_time.required' => 'Start time is required',
            'start_time.date_format' => 'Start time must be in HH:MM format (e.g., 07:00)',
            'end_time.required'   => 'End time is required',
            'end_time.date_format' => 'End time must be in HH:MM format (e.g., 15:00)',
        ];
    }
}
