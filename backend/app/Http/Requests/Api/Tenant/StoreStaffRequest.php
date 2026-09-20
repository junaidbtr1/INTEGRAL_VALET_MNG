<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users')->where('tenant_id', $this->user()->tenant_id)],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['required', 'string', 'min:8'],
            'role'      => ['required', 'string'],
            'shift_id'  => ['required', 'integer', 'exists:shifts,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Staff name is required',
            'email.required'    => 'Email is required',
            'email.email'       => 'Please enter a valid email',
            'email.unique'      => 'A staff member with this email already exists',
            'password.required'  => 'Password is required',
            'password.min'       => 'Password must be at least 8 characters',
            'role.required'      => 'Role is required',
            'shift_id.required'  => 'Shift assignment is required',
            'shift_id.exists'    => 'The selected shift is invalid',
        ];
    }
}
