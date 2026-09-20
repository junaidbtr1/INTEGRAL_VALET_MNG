<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UpdateTenantSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            'parking'                              => ['sometimes', 'array'],
            'parking.pricing_mode'                 => ['sometimes', 'string', 'in:flat,tiered'],
            'parking.pricing_tiers'                => ['sometimes', 'array'],
            'parking.pricing_tiers.*.up_to_hours'  => ['nullable', 'integer', 'min:1'],
            'parking.pricing_tiers.*.rate_per_hour'=> ['required_with:parking.pricing_tiers', 'integer', 'min:0'],
            'parking.default_rate_per_hour'        => ['sometimes', 'integer', 'min:0'],
            'parking.grace_period_minutes'         => ['sometimes', 'integer', 'min:0'],
            'parking.tax_rate'                     => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'parking.operating_hours'              => ['sometimes', 'array'],
            'parking.operating_hours.start'        => ['sometimes', 'date_format:H:i'],
            'parking.operating_hours.end'          => ['sometimes', 'date_format:H:i'],
            'parking.overstay_after_hours'         => ['sometimes', 'integer', 'min:1'],
            'parking.overstay_surcharge'           => ['sometimes', 'integer', 'min:0'],
            'parking.lost_ticket_penalty'          => ['sometimes', 'integer', 'min:0'],
            'ticket'                               => ['sometimes', 'array'],
            'ticket.auto_close_after_hours'        => ['sometimes', 'integer', 'min:1'],
        ];
    }

    protected function failedValidation(Validator $validator): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors()->toArray(),
        ], 422));
    }
}
