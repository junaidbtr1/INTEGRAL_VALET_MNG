<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_super_admin' => $this->is_super_admin,
            'is_building_owner' => $this->is_building_owner,
            'is_active' => $this->is_active,
            'avatar_url' => $this->avatar_url,
            'timezone' => $this->timezone,
            'locale' => $this->locale,
            'two_factor_enabled' => $this->two_factor_enabled,
            'force_password_change' => $this->force_password_change,
            'last_login_at' => $this->last_login_at?->toISOString(),
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'shift_id' => $this->shift_id,
            'shift'    => $this->whenLoaded('shift', fn () => [
                'id'   => $this->shift->id,
                'name' => $this->shift->name,
            ]),
            'roles' => $this->whenLoaded('roles', fn () => $this->getRoleNames()),
            'permissions' => $this->when(
                $request->routeIs('auth.me'),
                fn () => $this->getAllPermissions()->pluck('name')
            ),
            'tenant' => new TenantResource($this->whenLoaded('tenant')),
            'owned_buildings' => TenantResource::collection($this->whenLoaded('ownedBuildings')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
