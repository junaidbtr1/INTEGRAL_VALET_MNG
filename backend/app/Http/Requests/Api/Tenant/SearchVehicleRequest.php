<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;

class SearchVehicleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:2'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'Search query is required',
            'q.min'      => 'Search query must be at least 2 characters',
        ];
    }
}
