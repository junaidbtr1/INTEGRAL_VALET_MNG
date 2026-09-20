<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Tenant;

use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'max:255'],
            'email'     => ['sometimes', 'email', 'max:255', Rule::unique('users')->where('tenant_id', $this->user()->tenant_id)->ignore($this->route('staff'))],
            'phone'     => ['nullable', 'string', 'max:20'],
            'password'  => ['nullable', 'string', 'min:8'],
            'role'      => ['sometimes', 'string'],
            'shift_id'  => ['sometimes', 'nullable', 'integer', 'exists:shifts,id'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'     => 'Please enter a valid email',
            'email.unique'    => 'A staff member with this email already exists',
            'password.min'    => 'Password must be at least 8 characters',
            'shift_id.exists' => 'The selected shift is invalid',
        ];
    }
}
