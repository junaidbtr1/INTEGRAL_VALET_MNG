<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;

class RefundPaymentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Refund reason is required',
        ];
    }
}
