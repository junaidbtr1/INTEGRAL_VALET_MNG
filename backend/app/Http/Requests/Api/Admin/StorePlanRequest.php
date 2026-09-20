<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use App\Http\Requests\Api\BaseFormRequest;

class StorePlanRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'price_monthly'       => ['required', 'integer', 'min:0'],
            'price_yearly'        => ['required', 'integer', 'min:0'],
            'currency'            => ['sometimes', 'string', 'max:3'],
            'max_slots'           => ['required', 'integer', 'min:1'],
            'max_staff_users'     => ['required', 'integer', 'min:1'],
            'max_tickets_per_day' => ['required', 'integer', 'min:1'],
            'features'            => ['nullable', 'array'],
            'is_active'           => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'                => 'Plan name is required',
            'price_monthly.required'       => 'Monthly price is required',
            'price_monthly.min'            => 'Monthly price cannot be negative',
            'price_yearly.required'        => 'Yearly price is required',
            'price_yearly.min'             => 'Yearly price cannot be negative',
            'max_slots.required'           => 'Maximum slots is required',
            'max_slots.min'                => 'Maximum slots must be at least 1',
            'max_staff_users.required'     => 'Maximum staff users is required',
            'max_staff_users.min'          => 'Maximum staff users must be at least 1',
            'max_tickets_per_day.required' => 'Maximum tickets per day is required',
            'max_tickets_per_day.min'      => 'Maximum tickets per day must be at least 1',
        ];
    }
}
