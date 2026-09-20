<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\TenantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreTenantRequest;
use App\Http\Requests\Api\Admin\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Tenant::with('plan', 'owner')
            ->withCount(['users', 'tickets', 'parkingSlots']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDir = str_starts_with($sortField, '-') ? 'desc' : 'asc';
        $sortField = ltrim($sortField, '-');
        $query->orderBy($sortField, $sortDir);

        $perPage = min((int) $request->input('per_page', 15), 100);
        $tenants = $query->paginate($perPage);

        return $this->paginated(TenantResource::collection($tenants), 'Tenants retrieved successfully');
    }

    public function store(StoreTenantRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['owner_id'])) {
            $owner = User::find($validated['owner_id']);
            if (! $owner || ! $owner->isBuildingOwner()) {
                return $this->error('The selected owner must be a building owner user.', 422, 'GENERAL_002');
            }
        }

        $tenant = Tenant::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'plan_id' => $validated['plan_id'] ?? null,
            'owner_id' => $validated['owner_id'] ?? null,
            'status' => TenantStatus::TRIAL,
            'trial_ends_at' => now()->addDays($validated['trial_days'] ?? 14),
            'features' => $this->defaultFeatures(),
            'settings' => $this->defaultSettings($validated['name']),
        ]);

        $this->seedDefaultShifts($tenant);

        $tenant->load('plan', 'owner');
        $tenant->loadCount(['users', 'tickets', 'parkingSlots']);

        return $this->created(new TenantResource($tenant), 'Tenant created successfully');
    }

    public function show(Tenant $tenant): JsonResponse
    {
        $tenant->load('plan', 'owner');
        $tenant->loadCount(['users', 'tickets', 'parkingSlots']);

        return $this->success(new TenantResource($tenant));
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): JsonResponse
    {
        $validated = $request->validated();

        if (isset($validated['owner_id'])) {
            $owner = User::find($validated['owner_id']);
            if (! $owner || ! $owner->isBuildingOwner()) {
                return $this->error('The selected owner must be a building owner user.', 422, 'GENERAL_002');
            }
        }

        $tenant->update($validated);
        $tenant->load('plan', 'owner');
        $tenant->loadCount(['users', 'tickets', 'parkingSlots']);

        return $this->success(new TenantResource($tenant), 'Tenant updated successfully');
    }

    public function destroy(Tenant $tenant): JsonResponse|Response
    {
        try {
            $tenant->update(['status' => TenantStatus::CANCELLED]);
            $tenant->delete();

            return $this->noContent();
        } catch (\Throwable $e) {
            return $this->error('Failed to delete tenant: ' . $e->getMessage(), 500, 'TENANT_DELETE_FAILED');
        }
    }

    private function seedDefaultShifts(Tenant $tenant): void
    {
        $shifts = [
            ['name' => 'Morning',   'start_time' => '07:00', 'end_time' => '15:00'],
            ['name' => 'Afternoon', 'start_time' => '15:00', 'end_time' => '23:00'],
            ['name' => 'Night',     'start_time' => '23:00', 'end_time' => '07:00'],
        ];

        foreach ($shifts as $shift) {
            Shift::create([
                'tenant_id'  => $tenant->id,
                'name'       => $shift['name'],
                'start_time' => $shift['start_time'],
                'end_time'   => $shift['end_time'],
                'is_active'  => true,
            ]);
        }
    }

    private function defaultFeatures(): array
    {
        return [
            'coupon_system' => true,
            'chargeback' => false,
            'ticket_printing' => true,
            'qr_code' => true,
            'barcode' => false,
            'sms_notifications' => false,
            'email_notifications' => true,
            'valet_tracking' => false,
            'customer_feedback' => false,
            'vehicle_photo_capture' => false,
            'multi_floor' => false,
            'reserved_slots' => false,
        ];
    }

    private function defaultSettings(string $name): array
    {
        return [
            'parking' => [
                'default_rate_per_hour' => 500,
                'currency' => 'USD',
                'timezone' => 'America/New_York',
                'operating_hours' => ['start' => '06:00', 'end' => '23:00'],
            ],
            'ticket' => [
                'prefix' => strtoupper(substr($name, 0, 3)),
                'auto_close_after_hours' => 24,
            ],
        ];
    }
}
