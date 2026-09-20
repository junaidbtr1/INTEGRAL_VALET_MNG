<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use App\Http\Requests\Api\BaseFormRequest;

class UpdatePlanRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'                => ['sometimes', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:1000'],
            'price_monthly'       => ['sometimes', 'integer', 'min:0'],
            'price_yearly'        => ['sometimes', 'integer', 'min:0'],
            'max_slots'           => ['sometimes', 'integer', 'min:1'],
            'max_staff_users'     => ['sometimes', 'integer', 'min:1'],
            'max_tickets_per_day' => ['sometimes', 'integer', 'min:1'],
            'features'            => ['nullable', 'array'],
            'is_active'           => ['sometimes', 'boolean'],
        ];
    }
}
