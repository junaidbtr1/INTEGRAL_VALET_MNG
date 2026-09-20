<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Enums\TicketStatus;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketStatusRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(TicketStatus::class)->except([
                TicketStatus::CREATED,
                TicketStatus::ACTIVE,
                TicketStatus::OVERSTAY,
            ])],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required',
            'status.enum'     => 'Invalid status transition',
        ];
    }
}
