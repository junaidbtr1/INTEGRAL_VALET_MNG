<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\SlotType;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreParkingSlotRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'floor'                 => ['required', 'string', 'max:10'],
            'zone'                  => ['required', 'string', 'max:10'],
            'slot_number'           => [
                'required',
                'string',
                'max:20',
                Rule::unique('parking_slots')->where(function ($query) {
                    return $query
                        ->where('tenant_id', $this->user()->tenant_id)
                        ->where('floor', $this->input('floor'))
                        ->where('zone', $this->input('zone'));
                }),
            ],
            'slot_type'             => ['sometimes', Rule::enum(SlotType::class)],
            'vehicle_types_allowed' => ['nullable', 'array'],
            'is_covered'            => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'floor.required'        => 'Floor is required',
            'zone.required'         => 'Zone is required',
            'slot_number.required'  => 'Slot number is required',
            'slot_number.unique'    => 'This slot number already exists in this floor/zone',
            'slot_type.enum'        => 'Invalid slot type',
        ];
    }
}
