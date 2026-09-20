<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin;

use App\Enums\TenantStatus;
use App\Http\Requests\Api\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends BaseFormRequest
{
    public function rules(): array
    {
        $tenantId = $this->route('tenant')?->id ?? $this->route('tenant');

        return [
            'name'            => ['sometimes', 'string', 'max:255'],
            'email'           => ['sometimes', 'email', Rule::unique('tenants', 'email')->ignore($tenantId)],
            'phone'           => ['nullable', 'string', 'max:20'],
            'address'         => ['nullable', 'string', 'max:500'],
            'plan_id'         => ['nullable', 'exists:plans,id'],
            'owner_id'        => ['nullable', 'exists:users,id'],
            'status'          => ['sometimes', Rule::enum(TenantStatus::class)],
            'logo_url'        => ['nullable', 'string'],
            'primary_color'   => ['nullable', 'string', 'max:7'],
            'secondary_color' => ['nullable', 'string', 'max:7'],
            'accent_color'    => ['nullable', 'string', 'max:7'],
            'features'        => ['nullable', 'array'],
            'settings'        => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique'   => 'A tenant with this email already exists',
            'status.enum'    => 'Invalid status',
            'plan_id.exists' => 'Selected plan does not exist',
        ];
    }
}
