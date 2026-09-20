<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $isBuildingOwner = $this->boolean('is_building_owner');

        return [
            'is_building_owner' => ['sometimes', 'boolean'],
            'tenant_id'         => [$isBuildingOwner ? 'nullable' : 'required', 'exists:tenants,id'],
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone'             => ['nullable', 'string', 'max:20'],
            'password'          => ['required', 'string', 'min:8'],
            'role'              => [$isBuildingOwner ? 'nullable' : 'required', 'string', Rule::in(['tenant_admin'])],
            'is_active'         => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'tenant_id.required' => 'Tenant is required',
            'tenant_id.exists'   => 'Selected tenant does not exist',
            'name.required'      => 'Name is required',
            'email.required'     => 'Email is required',
            'email.email'        => 'Please enter a valid email address',
            'email.unique'       => 'A user with this email already exists in this tenant',
            'password.required'  => 'Password is required',
            'password.min'       => 'Password must be at least 8 characters',
            'role.required' => 'Role is required',
            'role.in'       => 'Super admin can only assign the tenant_admin role',
        ];
    }
}
