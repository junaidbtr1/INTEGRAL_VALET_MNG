<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'error_code' => 'AUTH_003',
            ], 401);
        }

        // Building owner path — auto-resolve tenant from the header if provided,
        // otherwise fall back to the owner's first assigned building.
        if ($user->isBuildingOwner()) {
            $headerTenantId = (int) $request->header('X-Tenant-ID');

            if ($headerTenantId) {
                $owns = Tenant::where('id', $headerTenantId)
                    ->where('owner_id', $user->id)
                    ->exists();

                if (! $owns) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Access denied to this building.',
                        'error_code' => 'TENANT_001',
                    ], 403);
                }

                $tenantId = $headerTenantId;
            } else {
                $firstBuilding = Tenant::where('owner_id', $user->id)->first();

                if (! $firstBuilding) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No buildings are assigned to your account.',
                        'error_code' => 'TENANT_001',
                    ], 403);
                }

                $tenantId = $firstBuilding->id;
            }

            app()[PermissionRegistrar::class]->setPermissionsTeamId($tenantId);

            return $next($request);
        }

        // Regular staff path
        if (! $user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant access required.',
                'error_code' => 'TENANT_001',
            ], 403);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Account is deactivated.',
                'error_code' => 'AUTH_004',
            ], 403);
        }

        // Set Spatie team context
        app()[PermissionRegistrar::class]->setPermissionsTeamId($user->tenant_id);

        return $next($request);
    }
}
