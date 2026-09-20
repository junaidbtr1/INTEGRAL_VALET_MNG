<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\CouponType;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreCouponRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper($this->input('code', ''))]);
        }
    }

    public function rules(): array
    {
        return [
            'code'                     => ['required', 'string', 'min:4', 'max:20', 'regex:/^[A-Z0-9]+$/', Rule::unique('coupons')->where('tenant_id', $this->user()->tenant_id)],
            'type'                     => ['required', Rule::enum(CouponType::class)],
            'value'                    => ['required', 'integer', 'min:1'],
            'min_amount'               => ['nullable', 'integer', 'min:0'],
            'max_discount'             => ['nullable', 'integer', 'min:0'],
            'usage_limit'              => ['nullable', 'integer', 'min:1'],
            'per_user_limit'           => ['nullable', 'integer', 'min:1'],
            'applicable_vehicle_types' => ['nullable', 'array'],
            'valid_from'               => ['nullable', 'date'],
            'valid_until'              => ['nullable', 'date', 'after_or_equal:valid_from'],
            'description'              => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'  => 'Coupon code is required',
            'code.regex'     => 'Code must be uppercase letters and numbers only',
            'code.unique'    => 'This coupon code already exists',
            'type.required'  => 'Coupon type is required',
            'type.enum'      => 'Invalid coupon type',
            'value.required' => 'Value is required',
        ];
    }
}
