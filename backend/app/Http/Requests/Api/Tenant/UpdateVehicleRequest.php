<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\VehicleType;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'vehicle_type'    => ['sometimes', Rule::enum(VehicleType::class)],
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
            'vehicle_type.enum' => 'Invalid vehicle type',
        ];
    }
}
