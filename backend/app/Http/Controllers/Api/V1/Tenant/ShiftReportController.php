<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Enums\PaymentStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftReportController extends Controller
{
    use ResolvesActiveTenant;

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'from'     => ['required', 'date'],
            'to'       => ['required', 'date', 'after_or_equal:from'],
            'staff_id' => ['nullable', 'integer'],
            'shift_id' => ['nullable', 'integer'],
        ]);

        $tenantId = $this->getActiveTenantId($request);
        $from     = $request->date('from')->startOfDay();
        $to       = $request->date('to')->endOfDay();
        $user     = $request->user();

        $staffQuery = User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('shift');

        if (! $user->is_building_owner && ! $user->hasPermissionTo('users.view')) {
            $staffQuery->where('id', $user->id);
        }

        if ($request->filled('staff_id')) {
            $staffQuery->where('id', (int) $request->input('staff_id'));
        }

        if ($request->filled('shift_id')) {
            $staffQuery->where('shift_id', (int) $request->input('shift_id'));
        }

        $staffMembers = $staffQuery->get();

        $ticketsByStaff = Ticket::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('created_by', $staffMembers->pluck('id'))
            ->select(
                'created_by',
                DB::raw('COUNT(*) as tickets_created'),
                DB::raw('SUM(CASE WHEN status IN ("' . TicketStatus::CLOSED->value . '","' . TicketStatus::COMPLETED->value . '") THEN 1 ELSE 0 END) as tickets_closed'),
                DB::raw('AVG(duration_minutes) as avg_duration')
            )
            ->groupBy('created_by')
            ->get()
            ->keyBy('created_by');

        $vehiclesByStaff = Ticket::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('created_by', $staffMembers->pluck('id'))
            ->select('created_by', 'vehicle_type', DB::raw('COUNT(*) as count'))
            ->groupBy('created_by', 'vehicle_type')
            ->get()
            ->groupBy('created_by');

        $paymentsByStaff = Payment::where('tenant_id', $tenantId)
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('processed_by', $staffMembers->pluck('id'))
            ->select('processed_by', 'payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as amount'))
            ->groupBy('processed_by', 'payment_method')
            ->get()
            ->groupBy('processed_by');

        $revenueByStaff = Payment::where('tenant_id', $tenantId)
            ->where('status', PaymentStatus::COMPLETED)
            ->whereBetween('created_at', [$from, $to])
            ->whereIn('processed_by', $staffMembers->pluck('id'))
            ->select('processed_by', DB::raw('SUM(amount) as total'))
            ->groupBy('processed_by')
            ->get()
            ->keyBy('processed_by');

        $results = [];

        foreach ($staffMembers as $staff) {
            $ticketRow  = $ticketsByStaff->get($staff->id);
            $paymentRow = $revenueByStaff->get($staff->id);

            $ticketsCreated  = (int) ($ticketRow?->tickets_created ?? 0);
            $totalRevenue    = (int) ($paymentRow?->total ?? 0);

            if ($ticketsCreated === 0 && $totalRevenue === 0) {
                continue;
            }

            $byMethod = ($paymentsByStaff->get($staff->id) ?? collect())->map(fn ($row) => [
                'method' => $row->payment_method,
                'count'  => (int) $row->count,
                'amount' => (int) $row->amount,
            ])->values()->toArray();

            $byType = ($vehiclesByStaff->get($staff->id) ?? collect())->map(fn ($row) => [
                'type'  => $row->vehicle_type,
                'count' => (int) $row->count,
            ])->values()->toArray();

            $results[] = [
                'staff' => [
                    'id'   => $staff->id,
                    'name' => $staff->name,
                    'role' => $staff->getRoleNames()->first() ?? 'staff',
                ],
                'shift' => $staff->shift ? [
                    'id'         => $staff->shift->id,
                    'name'       => $staff->shift->name,
                    'start_time' => $staff->shift->start_time,
                    'end_time'   => $staff->shift->end_time,
                ] : null,
                'stats' => [
                    'tickets_created'     => $ticketsCreated,
                    'tickets_closed'      => (int) ($ticketRow?->tickets_closed ?? 0),
                    'total_revenue'       => $totalRevenue,
                    'avg_duration_minutes'=> $ticketRow?->avg_duration !== null
                        ? (int) round((float) $ticketRow->avg_duration)
                        : null,
                    'by_payment_method'   => $byMethod,
                    'by_vehicle_type'     => $byType,
                ],
            ];
        }

        return $this->success($results, 'Shift report retrieved successfully');
    }
}
