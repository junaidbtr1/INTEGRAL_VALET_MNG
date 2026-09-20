<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Tenant;
use Illuminate\Http\Request;

trait ResolvesActiveTenant
{
    protected function getActiveTenantId(Request $request): int
    {
        $user = $request->user();

        return $user->isBuildingOwner()
            ? (int) $request->header('X-Tenant-ID')
            : (int) $user->tenant_id;
    }

    protected function getActiveTenant(Request $request): Tenant
    {
        return Tenant::with('plan')->findOrFail($this->getActiveTenantId($request));
    }
}
