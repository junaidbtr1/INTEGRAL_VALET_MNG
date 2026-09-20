<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\CouponType;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'type'                     => ['sometimes', Rule::enum(CouponType::class)],
            'value'                    => ['sometimes', 'integer', 'min:1'],
            'min_amount'               => ['nullable', 'integer', 'min:0'],
            'max_discount'             => ['nullable', 'integer', 'min:0'],
            'usage_limit'              => ['nullable', 'integer', 'min:1'],
            'per_user_limit'           => ['nullable', 'integer', 'min:1'],
            'applicable_vehicle_types' => ['nullable', 'array'],
            'valid_from'               => ['nullable', 'date'],
            'valid_until'              => ['nullable', 'date'],
            'description'              => ['nullable', 'string', 'max:500'],
            'is_active'                => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.enum' => 'Invalid coupon type',
        ];
    }
}
