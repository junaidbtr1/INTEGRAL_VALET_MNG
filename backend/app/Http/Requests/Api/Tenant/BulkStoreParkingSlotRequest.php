<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\SlotType;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class BulkStoreParkingSlotRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'floor'     => ['required', 'string', 'max:10'],
            'zone'      => ['required', 'string', 'max:10'],
            'prefix'    => ['required', 'string', 'max:10'],
            'start'     => ['required', 'integer', 'min:1'],
            'end'       => ['required', 'integer', 'min:1', 'gte:start'],
            'slot_type' => ['sometimes', Rule::enum(SlotType::class)],
            'is_covered'=> ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'floor.required'   => 'Floor is required',
            'zone.required'    => 'Zone is required',
            'prefix.required'  => 'Prefix is required',
            'start.required'   => 'Start number is required',
            'end.required'     => 'End number is required',
            'end.gte'          => 'End must be greater than or equal to start',
            'slot_type.enum'   => 'Invalid slot type',
        ];
    }
}
