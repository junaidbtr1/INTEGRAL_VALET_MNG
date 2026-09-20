<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\StoreStaffRequest;
use App\Http\Requests\Api\Tenant\UpdateStaffRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class StaffController extends Controller
{
    use ResolvesActiveTenant;

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);

        $query = User::with(['roles', 'shift'])
            ->where('tenant_id', $tenantId)
            ->where('id', '!=', $request->user()->id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $users   = $query->latest()->paginate($perPage);

        return $this->paginated(UserResource::collection($users), 'Staff retrieved successfully');
    }

    public function store(StoreStaffRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenant    = $this->getActiveTenant($request);
        $tenantId  = $tenant->id;

        $maxStaff    = $tenant->plan?->max_staff_users ?? PHP_INT_MAX;
        $currentStaff = User::where('tenant_id', $tenantId)->count();

        if ($currentStaff >= $maxStaff) {
            return $this->error('Staff limit reached for your plan.', 402, 'TENANT_004');
        }

        app()[PermissionRegistrar::class]->setPermissionsTeamId($tenantId);
        $roleExists = Role::where('guard_name', 'sanctum')
            ->where('name', $validated['role'])
            ->where(function ($q) use ($tenantId) {
                $q->whereNull('tenant_id')
                  ->orWhere('tenant_id', $tenantId);
            })
            ->exists();

        if (! $roleExists) {
            return $this->validationError(
                ['role' => ['Selected role does not exist.']],
            );
        }

        $user = User::create([
            'tenant_id'         => $tenantId,
            'shift_id'          => $validated['shift_id'],
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'phone'             => $validated['phone'] ?? null,
            'password'          => $validated['password'],
            'is_active'         => $validated['is_active'] ?? true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);
        $user->load(['roles', 'shift']);

        return $this->created(new UserResource($user), 'Staff member created successfully');
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $user = User::with(['roles', 'shift'])
            ->where('tenant_id', $this->getActiveTenantId($request))
            ->findOrFail($id);

        return $this->success(new UserResource($user));
    }

    public function update(int $id, UpdateStaffRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenantId  = $this->getActiveTenantId($request);
        $user      = User::where('tenant_id', $tenantId)->findOrFail($id);

        $updateData = collect($validated)->except(['role', 'password'])->toArray();
        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);

        if (isset($validated['role'])) {
            app()[PermissionRegistrar::class]->setPermissionsTeamId($tenantId);
            $user->syncRoles([$validated['role']]);
        }

        if (isset($validated['is_active']) && ! $validated['is_active']) {
            $user->tokens()->delete();
        }

        $user->load(['roles', 'shift']);

        return $this->success(new UserResource($user), 'Staff member updated successfully');
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $user = User::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);

        if ($user->id === $request->user()->id) {
            return $this->error('Cannot delete your own account', 400, 'USER_004');
        }

        $user->update(['is_active' => false]);
        $user->tokens()->delete();
        $user->delete();

        return $this->noContent();
    }
}
