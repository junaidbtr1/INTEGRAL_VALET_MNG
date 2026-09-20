<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;

class UpdateRoleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'permissions.required' => 'At least one permission is required',
            'permissions.*.exists' => 'One or more selected permissions are invalid',
        ];
    }
}
