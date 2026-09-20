<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\VehicleType;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends BaseFormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('plate_number')) {
            $this->merge([
                'plate_number' => strtoupper(str_replace([' ', '-'], '', $this->input('plate_number', ''))),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'plate_number'    => ['required', 'string', 'max:20', Rule::unique('vehicles')->where('tenant_id', $this->user()->tenant_id)],
            'vehicle_type'    => ['required', Rule::enum(VehicleType::class)],
            'color'           => ['nullable', 'string', 'max:30'],
            'make'            => ['nullable', 'string', 'max:50'],
            'model'           => ['nullable', 'string', 'max:50'],
            'owner_name'      => ['nullable', 'string', 'max:100'],
            'owner_phone'     => ['nullable', 'string', 'max:20'],
            'is_vip'          => ['sometimes', 'boolean'],
            'is_blacklisted'  => ['sometimes', 'boolean'],
            'blacklist_reason'=> ['nullable', 'string', 'max:255'],
            'notes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'plate_number.required' => 'Plate number is required',
            'plate_number.unique'   => 'A vehicle with this plate number already exists',
            'vehicle_type.required' => 'Vehicle type is required',
            'vehicle_type.enum'     => 'Invalid vehicle type',
        ];
    }
}
