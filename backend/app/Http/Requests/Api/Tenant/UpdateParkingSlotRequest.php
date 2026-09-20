<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\SlotType;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateParkingSlotRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'slot_type'             => ['sometimes', Rule::enum(SlotType::class)],
            'vehicle_types_allowed' => ['nullable', 'array'],
            'is_covered'            => ['sometimes', 'boolean'],
            'sort_order'            => ['sometimes', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'slot_type.enum' => 'Invalid slot type',
        ];
    }
}
