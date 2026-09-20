<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Enums\SlotStatus;
use App\Enums\SlotType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\BulkStoreParkingSlotRequest;
use App\Http\Requests\Api\Tenant\StoreParkingSlotRequest;
use App\Http\Requests\Api\Tenant\UpdateParkingSlotRequest;
use App\Http\Requests\Api\Tenant\UpdateSlotStatusRequest;
use App\Models\ParkingSlot;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParkingSlotController extends Controller
{
    use ResolvesActiveTenant;

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);

        $query = ParkingSlot::where('tenant_id', $tenantId);

        if ($request->filled('floor')) {
            $query->where('floor', $request->input('floor'));
        }

        if ($request->filled('zone')) {
            $query->where('zone', $request->input('zone'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('slot_type')) {
            $query->where('slot_type', $request->input('slot_type'));
        }

        if ($request->filled('search')) {
            $query->where('slot_number', 'like', '%' . $request->input('search') . '%');
        }

        $slots = $query->orderBy('floor')
            ->orderBy('zone')
            ->orderBy('sort_order')
            ->orderBy('slot_number')
            ->get();

        return $this->success($slots, 'Parking slots retrieved successfully');
    }

    public function store(StoreParkingSlotRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenant    = $this->getActiveTenant($request);
        $tenantId  = $tenant->id;

        $maxSlots    = $tenant->plan?->max_slots ?? PHP_INT_MAX;
        $currentSlots = ParkingSlot::where('tenant_id', $tenantId)->count();

        if ($currentSlots >= $maxSlots) {
            return $this->error('Slot limit reached for your plan.', 402, 'TENANT_004');
        }

        $slot = ParkingSlot::create([
            'tenant_id'             => $tenantId,
            'floor'                 => $validated['floor'],
            'zone'                  => $validated['zone'],
            'slot_number'           => $validated['slot_number'],
            'slot_type'             => $validated['slot_type'] ?? SlotType::STANDARD,
            'vehicle_types_allowed' => $validated['vehicle_types_allowed'] ?? null,
            'is_covered'            => $validated['is_covered'] ?? false,
            'status'                => SlotStatus::AVAILABLE,
        ]);

        return $this->created($slot, 'Parking slot created successfully');
    }

    public function bulkStore(BulkStoreParkingSlotRequest $request): JsonResponse
    {
        $validated   = $request->validated();
        $tenant      = $this->getActiveTenant($request);
        $tenantId    = $tenant->id;
        $created     = 0;
        $skipped     = 0;

        $maxSlots      = $tenant->plan?->max_slots ?? PHP_INT_MAX;
        $currentSlots  = ParkingSlot::where('tenant_id', $tenantId)->count();
        $newSlotsCount = $validated['end'] - $validated['start'] + 1;

        if ($currentSlots + $newSlotsCount > $maxSlots) {
            return $this->error('Slot limit reached for your plan. Adding these slots would exceed your limit.', 402, 'TENANT_004');
        }

        for ($i = $validated['start']; $i <= $validated['end']; $i++) {
            $slotNumber = $validated['prefix'] . '-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT);

            $exists = ParkingSlot::where('tenant_id', $tenantId)
                ->where('floor', $validated['floor'])
                ->where('zone', $validated['zone'])
                ->where('slot_number', $slotNumber)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            ParkingSlot::create([
                'tenant_id'  => $tenantId,
                'floor'      => $validated['floor'],
                'zone'       => $validated['zone'],
                'slot_number'=> $slotNumber,
                'slot_type'  => $validated['slot_type'] ?? SlotType::STANDARD,
                'is_covered' => $validated['is_covered'] ?? false,
                'status'     => SlotStatus::AVAILABLE,
                'sort_order' => $i,
            ]);
            $created++;
        }

        return $this->created([
            'created' => $created,
            'skipped' => $skipped,
        ], "{$created} slots created, {$skipped} skipped (already exist)");
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $slot = ParkingSlot::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);

        return $this->success($slot);
    }

    public function update(int $id, UpdateParkingSlotRequest $request): JsonResponse
    {
        $slot = ParkingSlot::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);
        $slot->update($request->validated());

        return $this->success($slot, 'Parking slot updated successfully');
    }

    public function updateStatus(int $id, UpdateSlotStatusRequest $request): JsonResponse
    {
        $slot = ParkingSlot::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);

        if ($slot->status === SlotStatus::OCCUPIED) {
            return $this->error('Cannot change status of an occupied slot. Close the ticket first.', 409, 'SLOT_002');
        }

        $slot->update(['status' => $request->validated('status')]);

        return $this->success($slot, 'Slot status updated');
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $slot = ParkingSlot::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);

        if ($slot->status === SlotStatus::OCCUPIED) {
            return $this->error('Cannot delete an occupied slot.', 409, 'SLOT_002');
        }

        $slot->delete();

        return $this->noContent();
    }

    public function summary(Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);

        $total       = ParkingSlot::where('tenant_id', $tenantId)->count();
        $available   = ParkingSlot::where('tenant_id', $tenantId)->where('status', SlotStatus::AVAILABLE)->count();
        $occupied    = ParkingSlot::where('tenant_id', $tenantId)->where('status', SlotStatus::OCCUPIED)->count();
        $reserved    = ParkingSlot::where('tenant_id', $tenantId)->where('status', SlotStatus::RESERVED)->count();
        $maintenance = ParkingSlot::where('tenant_id', $tenantId)->where('status', SlotStatus::MAINTENANCE)->count();

        $floors = ParkingSlot::where('tenant_id', $tenantId)
            ->selectRaw('floor, COUNT(*) as total, SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available')
            ->groupBy('floor')
            ->orderBy('floor')
            ->get();

        return $this->success([
            'total'            => $total,
            'available'        => $available,
            'occupied'         => $occupied,
            'reserved'         => $reserved,
            'maintenance'      => $maintenance,
            'occupancy_percent'=> $total > 0 ? round(($occupied / $total) * 100, 1) : 0,
            'floors'           => $floors,
        ]);
    }
}
