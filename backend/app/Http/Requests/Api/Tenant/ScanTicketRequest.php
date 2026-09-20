<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;

class ScanTicketRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'ticket_number' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'ticket_number.required' => 'Ticket number is required',
        ];
    }
}
