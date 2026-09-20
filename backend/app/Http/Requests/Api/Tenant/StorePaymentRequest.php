<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\PaymentMethod;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'ticket_id'      => ['required', 'integer', 'exists:tickets,id'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)->except([PaymentMethod::COUPON])],
            'coupon_code'    => ['nullable', 'string'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_id.required'      => 'Ticket is required',
            'ticket_id.exists'        => 'Ticket not found',
            'payment_method.required' => 'Payment method is required',
            'payment_method.enum'     => 'Invalid payment method',
        ];
    }
}
