<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;

class StoreRoleRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'          => ['required', 'string', 'max:50', 'regex:/^[a-z_]+$/'],
            'description'   => ['nullable', 'string', 'max:255'],
            'permissions'   => ['required', 'array', 'min:1'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Role name is required',
            'name.regex'             => 'Role name must be lowercase letters and underscores only (e.g., floor_manager)',
            'permissions.required'   => 'At least one permission is required',
            'permissions.min'        => 'At least one permission is required',
            'permissions.*.exists'   => 'One or more selected permissions are invalid',
        ];
    }
}
