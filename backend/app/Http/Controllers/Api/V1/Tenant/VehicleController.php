<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\SearchVehicleRequest;
use App\Http\Requests\Api\Tenant\StoreVehicleRequest;
use App\Http\Requests\Api\Tenant\UpdateVehicleRequest;
use App\Models\Vehicle;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    use ResolvesActiveTenant;

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);

        $query = Vehicle::where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('plate_number', 'like', "%{$search}%")
                  ->orWhere('owner_name', 'like', "%{$search}%")
                  ->orWhere('owner_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->input('vehicle_type'));
        }

        if ($request->filled('is_vip')) {
            $query->where('is_vip', $request->boolean('is_vip'));
        }

        if ($request->filled('is_blacklisted')) {
            $query->where('is_blacklisted', $request->boolean('is_blacklisted'));
        }

        $perPage  = min((int) $request->input('per_page', 15), 100);
        $vehicles = $query->latest()->paginate($perPage);

        return $this->paginated($vehicles, 'Vehicles retrieved successfully');
    }

    public function store(StoreVehicleRequest $request): JsonResponse
    {
        $validated   = $request->validated();
        $tenantId    = $this->getActiveTenantId($request);
        $plateNumber = $validated['plate_number'];

        $vehicle = Vehicle::create([
            'tenant_id'       => $tenantId,
            'plate_number'    => $plateNumber,
            'vehicle_type'    => $validated['vehicle_type'],
            'color'           => $validated['color'] ?? null,
            'make'            => $validated['make'] ?? null,
            'model'           => $validated['model'] ?? null,
            'owner_name'      => $validated['owner_name'] ?? null,
            'owner_phone'     => $validated['owner_phone'] ?? null,
            'is_vip'          => $validated['is_vip'] ?? false,
            'is_blacklisted'  => $validated['is_blacklisted'] ?? false,
            'blacklist_reason'=> $validated['blacklist_reason'] ?? null,
            'notes'           => $validated['notes'] ?? null,
        ]);

        return $this->created($vehicle, 'Vehicle registered successfully');
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $vehicle = Vehicle::where('tenant_id', $this->getActiveTenantId($request))
            ->with(['tickets' => fn ($q) => $q->latest()->take(10)])
            ->findOrFail($id);

        return $this->success($vehicle);
    }

    public function update(int $id, UpdateVehicleRequest $request): JsonResponse
    {
        $vehicle = Vehicle::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);
        $vehicle->update($request->validated());

        return $this->success($vehicle, 'Vehicle updated successfully');
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $vehicle = Vehicle::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);
        $vehicle->delete();

        return $this->noContent();
    }

    public function search(SearchVehicleRequest $request): JsonResponse
    {
        $vehicles = Vehicle::where('tenant_id', $this->getActiveTenantId($request))
            ->where('plate_number', 'like', '%' . strtoupper($request->validated('q')) . '%')
            ->take(10)
            ->get(['id', 'plate_number', 'vehicle_type', 'color', 'owner_name', 'is_vip', 'is_blacklisted', 'visit_count']);

        return $this->success($vehicles);
    }
}
