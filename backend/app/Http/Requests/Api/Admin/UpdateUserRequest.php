<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $user     = $this->route('user');
        $tenantId = $user?->tenant_id;
        $userId   = $user?->id ?? $user;

        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'email', 'max:255', Rule::unique('users')->where('tenant_id', $tenantId)->ignore($userId)],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['nullable', 'string', 'min:8'],
            'role'      => ['sometimes', 'string', Rule::in(['tenant_admin'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'  => 'Please enter a valid email address',
            'email.unique' => 'A user with this email already exists in this tenant',
            'password.min' => 'Password must be at least 8 characters',
            'role.in'      => 'Super admin can only assign the tenant_admin role',
        ];
    }
}
