<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Enums\PaymentStatus;
use App\Enums\SlotStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\ParkingSlot;
use App\Traits\ResolvesActiveTenant;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantDashboardController extends Controller
{
    use ResolvesActiveTenant;

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);

        $totalSlots = ParkingSlot::where('tenant_id', $tenantId)->count();
        $availableSlots = ParkingSlot::where('tenant_id', $tenantId)->where('status', SlotStatus::AVAILABLE)->count();
        $occupiedSlots = ParkingSlot::where('tenant_id', $tenantId)->where('status', SlotStatus::OCCUPIED)->count();

        $ticketsToday = Ticket::where('tenant_id', $tenantId)->whereDate('created_at', today())->count();
        $ticketsMonth = Ticket::where('tenant_id', $tenantId)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();

        $activeTickets = Ticket::where('tenant_id', $tenantId)
            ->where(function ($q) {
                $q->where('status', TicketStatus::ACTIVE)
                  ->orWhere('status', TicketStatus::CREATED);
            })
            ->count();

        $revenueToday = Payment::where('tenant_id', $tenantId)->where('status', PaymentStatus::COMPLETED)->whereDate('created_at', today())->sum('amount');
        $revenueMonth = Payment::where('tenant_id', $tenantId)->where('status', PaymentStatus::COMPLETED)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount');
        $revenueYesterday = Payment::where('tenant_id', $tenantId)->where('status', PaymentStatus::COMPLETED)->whereDate('created_at', today()->subDay())->sum('amount');

        $recentTickets = Ticket::where('tenant_id', $tenantId)
            ->with(['creator:id,name', 'parkingSlot:id,slot_number'])
            ->latest()
            ->take(10)
            ->get(['id', 'ticket_number', 'vehicle_plate', 'vehicle_type', 'status', 'entry_at', 'parking_slot_id', 'created_by', 'created_at']);

        return $this->success([
            'stats' => [
                'tickets_today' => $ticketsToday,
                'tickets_month' => $ticketsMonth,
                'active_vehicles' => $activeTickets,
                'total_slots' => $totalSlots,
                'available_slots' => $availableSlots,
                'occupied_slots' => $occupiedSlots,
                'occupancy_percent' => $totalSlots > 0 ? round(($occupiedSlots / $totalSlots) * 100, 1) : 0,
                'revenue_today' => (int) $revenueToday,
                'revenue_yesterday' => (int) $revenueYesterday,
                'revenue_month' => (int) $revenueMonth,
            ],
            'recent_tickets' => $recentTickets,
        ]);
    }
}
