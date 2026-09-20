<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\VehicleType;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('vehicle_plate')) {
            $this->merge([
                'vehicle_plate' => strtoupper(str_replace([' ', '-'], '', $this->input('vehicle_plate', ''))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'vehicle_plate' => ['required', 'string', 'max:20'],
            'vehicle_type'  => ['required', Rule::enum(VehicleType::class)],
            'vehicle_color' => ['nullable', 'string', 'max:30'],
            'vehicle_make'  => ['nullable', 'string', 'max:50'],
            'parking_slot_id' => ['nullable', 'integer', 'exists:parking_slots,id'],
            'notes'         => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'vehicle_plate.required' => 'Vehicle plate number is required',
            'vehicle_type.required'  => 'Vehicle type is required',
            'vehicle_type.enum'      => 'Invalid vehicle type',
        ];
    }
}
