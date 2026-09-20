<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\StoreShiftRequest;
use App\Http\Requests\Api\Tenant\UpdateShiftRequest;
use App\Models\Shift;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    use ResolvesActiveTenant;
    public function index(Request $request): JsonResponse
    {
        $shifts = Shift::where('tenant_id', $this->getActiveTenantId($request))
            ->orderBy('start_time')
            ->get()
            ->map(fn (Shift $shift) => $this->formatShift($shift));

        return $this->success($shifts, 'Shifts retrieved successfully');
    }

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenantId  = $this->getActiveTenantId($request);

        $shift = Shift::create([
            'tenant_id'  => $tenantId,
            'name'       => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time'   => $validated['end_time'],
            'is_active'  => $validated['is_active'] ?? true,
        ]);

        return $this->created($this->formatShift($shift), 'Shift created successfully');
    }

    public function update(int $id, UpdateShiftRequest $request): JsonResponse
    {
        $shift = Shift::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);
        $shift->update($request->validated());

        return $this->success($this->formatShift($shift), 'Shift updated successfully');
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $shift = Shift::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);

        if ($shift->users()->count() > 0) {
            return $this->error('Cannot delete a shift that has staff assigned to it. Reassign staff first.', 409, 'SHIFT_001');
        }

        $shift->delete();

        return $this->noContent();
    }

    private function formatShift(Shift $shift): array
    {
        return [
            'id'          => $shift->id,
            'name'        => $shift->name,
            'start_time'  => $shift->start_time,
            'end_time'    => $shift->end_time,
            'is_active'   => $shift->is_active,
            'staff_count' => $shift->users()->count(),
            'created_at'  => $shift->created_at?->toISOString(),
        ];
    }
}
