<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreUserRequest;
use App\Http\Requests\Api\Admin\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\Permission\PermissionRegistrar;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('roles', 'tenant', 'ownedBuildings')
            ->where('is_super_admin', false);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->input('tenant_id'));
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_building_owner')) {
            $query->where('is_building_owner', $request->boolean('is_building_owner'));
        }

        // Return only building owners with no building assigned yet
        if ($request->boolean('unassigned_owners')) {
            $query->where('is_building_owner', true)->whereDoesntHave('ownedBuildings');
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $users   = $query->latest()->paginate($perPage);

        return $this->paginated(UserResource::collection($users), 'Users retrieved successfully');
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $isBuildingOwner = (bool) ($validated['is_building_owner'] ?? false);

        $user = User::create([
            'tenant_id'          => $isBuildingOwner ? null : $validated['tenant_id'],
            'name'               => $validated['name'],
            'email'              => $validated['email'],
            'phone'              => $validated['phone'] ?? null,
            'password'           => $validated['password'],
            'is_active'          => $validated['is_active'] ?? true,
            'is_building_owner'  => $isBuildingOwner,
            'email_verified_at'  => now(),
        ]);

        if (! $isBuildingOwner) {
            app()[PermissionRegistrar::class]->setPermissionsTeamId($validated['tenant_id']);
            $user->assignRole($validated['role']);
            $user->load('roles', 'tenant');
        } else {
            $user->load('ownedBuildings');
        }

        return $this->created(new UserResource($user), 'User created successfully');
    }

    public function show(User $user): JsonResponse
    {
        $user->load('roles', 'tenant', 'ownedBuildings');

        return $this->success(new UserResource($user));
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if ($user->is_super_admin) {
            return $this->error('Cannot modify super admin account', 403, 'USER_004');
        }

        $validated  = $request->validated();
        $updateData = collect($validated)->except(['role', 'password'])->toArray();

        if (! empty($validated['password'])) {
            $updateData['password'] = $validated['password'];
        }

        $user->update($updateData);

        if (isset($validated['role'])) {
            app()[PermissionRegistrar::class]->setPermissionsTeamId($user->tenant_id);
            $user->syncRoles([$validated['role']]);
        }

        $user->load('roles', 'tenant');

        return $this->success(new UserResource($user), 'User updated successfully');
    }

    public function destroy(User $user): JsonResponse|Response
    {
        if ($user->is_super_admin) {
            return $this->error('Cannot delete super admin account', 403, 'USER_004');
        }

        $user->update(['is_active' => false]);
        $user->tokens()->delete();
        $user->delete();

        return $this->noContent();
    }
}
