<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use App\Http\Requests\Api\BaseFormRequest;

class StoreTenantRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'address'    => ['nullable', 'string', 'max:500'],
            'plan_id'    => ['nullable', 'exists:plans,id'],
            'owner_id'   => ['nullable', 'exists:users,id'],
            'trial_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'Tenant name is required',
            'plan_id.exists'   => 'Selected plan does not exist',
            'trial_days.min'   => 'Trial days must be at least 1',
            'trial_days.max'   => 'Trial days cannot exceed 90',
        ];
    }
}
