<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\StoreRoleRequest;
use App\Http\Requests\Api\Tenant\UpdateRoleRequest;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    use ResolvesActiveTenant;

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);
        app()[PermissionRegistrar::class]->setPermissionsTeamId($tenantId);

        $systemRoles = Role::whereNull('tenant_id')
            ->where('name', '!=', 'tenant_admin')
            ->with('permissions')
            ->get()
            ->map(fn (Role $role) => [
                'id'          => $role->id,
                'name'        => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name'),
                'users_count' => $role->users()->where('users.tenant_id', $tenantId)->count(),
                'is_system'   => true,
                'created_at'  => $role->created_at?->toISOString(),
            ]);

        $customRoles = Role::where('tenant_id', $tenantId)
            ->with('permissions')
            ->get()
            ->map(fn (Role $role) => [
                'id'          => $role->id,
                'name'        => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name'),
                'users_count' => $role->users()->count(),
                'is_system'   => false,
                'created_at'  => $role->created_at?->toISOString(),
            ]);

        return $this->success([
            'system_roles' => $systemRoles,
            'custom_roles'  => $customRoles,
        ], 'Roles retrieved successfully');
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenantId  = $this->getActiveTenantId($request);

        app()[PermissionRegistrar::class]->setPermissionsTeamId($tenantId);

        $exists = Role::where('tenant_id', $tenantId)
            ->where('name', $validated['name'])
            ->where('guard_name', 'sanctum')
            ->exists();

        if ($exists) {
            return $this->validationError(
                ['name' => ['A role with this name already exists.']],
            );
        }

        $role = Role::create([
            'name'        => $validated['name'],
            'guard_name'  => 'sanctum',
            'tenant_id'   => $tenantId,
            'description' => $validated['description'] ?? null,
        ]);

        $role->syncPermissions($validated['permissions']);
        $role->load('permissions');

        return $this->created([
            'id'          => $role->id,
            'name'        => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('name'),
            'users_count' => 0,
            'created_at'  => $role->created_at?->toISOString(),
        ], 'Role created successfully');
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);
        app()[PermissionRegistrar::class]->setPermissionsTeamId($tenantId);

        $role = Role::where('tenant_id', $tenantId)->findOrFail($id);
        $role->load('permissions');

        return $this->success([
            'id'          => $role->id,
            'name'        => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('name'),
            'users_count' => $role->users()->count(),
            'created_at'  => $role->created_at?->toISOString(),
        ]);
    }

    public function update(int $id, UpdateRoleRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenantId  = $this->getActiveTenantId($request);

        app()[PermissionRegistrar::class]->setPermissionsTeamId($tenantId);

        $role = Role::where('tenant_id', $tenantId)->findOrFail($id);

        if (isset($validated['description'])) {
            $role->update(['description' => $validated['description']]);
        }

        $role->syncPermissions($validated['permissions']);
        $role->load('permissions');

        return $this->success([
            'id'          => $role->id,
            'name'        => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('name'),
            'users_count' => $role->users()->count(),
            'created_at'  => $role->created_at?->toISOString(),
        ], 'Role updated successfully');
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);
        app()[PermissionRegistrar::class]->setPermissionsTeamId($tenantId);

        $role = Role::where('tenant_id', $tenantId)->findOrFail($id);

        if ($role->users()->count() > 0) {
            return $this->error('Cannot delete role with assigned users. Reassign users first.', 409, 'ROLE_001');
        }

        $role->delete();

        return $this->noContent();
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::where('guard_name', 'sanctum')
            ->get()
            ->groupBy('group')
            ->map(fn ($group) => $group->pluck('name'));

        return $this->success($permissions, 'Permissions retrieved successfully');
    }
}
