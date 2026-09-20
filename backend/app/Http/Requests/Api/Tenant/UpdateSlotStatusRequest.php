<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\SlotStatus;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateSlotStatusRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(SlotStatus::class)->except([
                SlotStatus::OCCUPIED,
                SlotStatus::RESERVED,
            ])],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required',
            'status.enum'     => 'Invalid status. Allowed: available, maintenance, out_of_service',
        ];
    }
}
