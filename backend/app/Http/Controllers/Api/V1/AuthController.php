<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if (! $user || ! Hash::check($request->validated('password'), $user->password)) {
            if ($user) {
                $user->increment('failed_login_attempts');
                if ($user->failed_login_attempts >= config('auth.lockout.max_attempts')) {
                    $user->update(['locked_until' => now()->addMinutes(config('auth.lockout.lockout_minutes'))]);
                }
            }

            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->isLocked()) {
            return $this->error('Account is locked. Try again later.', 423, 'AUTH_002');
        }

        if (! $user->is_active) {
            return $this->error('Account is deactivated.', 403, 'AUTH_004');
        }

        if (! $user->is_super_admin && ! $user->isBuildingOwner()) {
            // Load tenant including soft-deleted ones so we can give a precise error
            $tenant = \App\Models\Tenant::withTrashed()->find($user->tenant_id);

            if ($tenant && $tenant->trashed()) {
                return $this->error('Your organization account has been deleted.', 403, 'TENANT_003');
            }

            if ($tenant && $tenant->status->value === 'cancelled') {
                return $this->error('Your organization account has been cancelled.', 403, 'TENANT_003');
            }

            if ($tenant && $tenant->isSuspended()) {
                return $this->error('Your organization account is suspended.', 403, 'TENANT_002');
            }
        }

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        if ($user->tenant_id) {
            setPermissionsTeamId($user->tenant_id);
        }

        $token = $user->createToken('api', ['*'], now()->addMinutes(
            (int) config('sanctum.token_expiry', 10080)
        ))->plainTextToken;

        if ($user->isBuildingOwner()) {
            $user->load('ownedBuildings.plan');
        } else {
            $user->load('tenant');
        }

        return $this->success([
            'token' => $token,
            'user' => new UserResource($user),
        ], 'Login successful');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->tenant_id) {
            setPermissionsTeamId($user->tenant_id);
        }

        if ($user->isBuildingOwner()) {
            $user->load('ownedBuildings.plan');
        } else {
            $user->load('tenant');
        }

        return $this->success([
            'user' => (new UserResource($user))->additional([]),
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(message: 'Logged out successfully');
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return $this->success(message: 'Logged out from all devices');
    }

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        $token = $user->createToken('api', ['*'], now()->addMinutes(
            (int) config('sanctum.token_expiry', 10080)
        ))->plainTextToken;

        return $this->success(['token' => $token], 'Token refreshed');
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->validated('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => $request->validated('password'),
            'password_changed_at' => now(),
            'force_password_change' => false,
        ]);

        return $this->success(message: 'Password changed successfully');
    }
}
