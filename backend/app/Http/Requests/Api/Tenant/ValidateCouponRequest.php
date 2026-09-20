<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;

class ValidateCouponRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Coupon code is required',
        ];
    }
}
