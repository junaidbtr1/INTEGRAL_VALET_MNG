<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Enums\SlotStatus;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\ScanTicketRequest;
use App\Http\Requests\Api\Tenant\StoreTicketRequest;
use App\Http\Requests\Api\Tenant\UpdateTicketStatusRequest;
use App\Models\ParkingSlot;
use App\Models\Ticket;
use App\Models\Vehicle;
use App\Services\PricingService;
use App\Services\TicketNumberGenerator;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    use ResolvesActiveTenant;

    public function __construct(
        private readonly TicketNumberGenerator $ticketNumberGenerator,
        private readonly PricingService $pricingService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $tenantId = $this->getActiveTenantId($request);

        $query = Ticket::where('tenant_id', $tenantId)
            ->with(['creator:id,name', 'closer:id,name', 'parkingSlot:id,slot_number,floor,zone']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_plate', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $perPage = min((int) $request->input('per_page', 15), 100);
        $tickets = $query->latest()->paginate($perPage);

        return $this->paginated($tickets, 'Tickets retrieved successfully');
    }

    public function store(StoreTicketRequest $request): JsonResponse
    {
        $validated   = $request->validated();
        $tenant      = $this->getActiveTenant($request);
        $tenantId    = $tenant->id;
        $plateNumber = $validated['vehicle_plate'];

        // Check plan daily ticket limit
        $maxPerDay  = $tenant->plan?->max_tickets_per_day ?? PHP_INT_MAX;
        $todayCount = Ticket::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= $maxPerDay) {
            return $this->error('Daily ticket limit reached for your plan.', 402, 'TENANT_004');
        }

        // Check blacklist
        $vehicle = Vehicle::where('tenant_id', $tenantId)
            ->where('plate_number', $plateNumber)
            ->first();

        if ($vehicle?->is_blacklisted) {
            return $this->error(
                'This vehicle is blacklisted: ' . ($vehicle->blacklist_reason ?? 'No reason provided'),
                403,
                'VEHICLE_001'
            );
        }

        // Check duplicate active ticket
        $activeTicket = Ticket::where('tenant_id', $tenantId)
            ->where('vehicle_plate', $plateNumber)
            ->where('status', '!=', TicketStatus::CLOSED)
            ->where('status', '!=', TicketStatus::CANCELLED)
            ->first();

        if ($activeTicket) {
            return $this->error(
                'This vehicle already has an active ticket: ' . $activeTicket->ticket_number,
                409,
                'TICKET_005'
            );
        }

        // Auto-assign slot if not provided
        $slotId = $validated['parking_slot_id'] ?? null;
        if (! $slotId) {
            $availableSlot = ParkingSlot::where('tenant_id', $tenantId)
                ->where('status', SlotStatus::AVAILABLE)
                ->orderBy('floor')
                ->orderBy('zone')
                ->orderBy('sort_order')
                ->first();

            if (! $availableSlot) {
                return $this->error('No available parking slots.', 409, 'SLOT_005');
            }

            $slotId = $availableSlot->id;
        } else {
            $slot = ParkingSlot::where('tenant_id', $tenantId)->find($slotId);
            if (! $slot || ! $slot->isAvailable()) {
                return $this->error('Selected slot is not available.', 409, 'SLOT_002');
            }
        }

        $ticket = DB::transaction(function () use ($tenantId, $plateNumber, $validated, $slotId, $vehicle, $request) {
            $ticketNumber = $this->ticketNumberGenerator->generate($tenantId);

            if (! $vehicle) {
                $vehicle = Vehicle::create([
                    'tenant_id'    => $tenantId,
                    'plate_number' => $plateNumber,
                    'vehicle_type' => $validated['vehicle_type'],
                    'color'        => $validated['vehicle_color'] ?? null,
                    'make'         => $validated['vehicle_make'] ?? null,
                ]);
            }

            $vehicle->increment('visit_count');
            $vehicle->update(['last_visit_at' => now()]);

            $ticket = Ticket::create([
                'tenant_id'      => $tenantId,
                'ticket_number'  => $ticketNumber,
                'vehicle_id'     => $vehicle->id,
                'parking_slot_id'=> $slotId,
                'created_by'     => $request->user()->id,
                'vehicle_plate'  => $plateNumber,
                'vehicle_type'   => $validated['vehicle_type'],
                'vehicle_color'  => $validated['vehicle_color'] ?? null,
                'vehicle_make'   => $validated['vehicle_make'] ?? null,
                'status'         => TicketStatus::ACTIVE,
                'entry_at'       => now(),
                'notes'          => $validated['notes'] ?? null,
            ]);

            ParkingSlot::where('id', $slotId)->update([
                'status'           => SlotStatus::OCCUPIED,
                'current_ticket_id'=> $ticket->id,
            ]);

            return $ticket;
        });

        $ticket->load(['creator:id,name', 'parkingSlot:id,slot_number,floor,zone']);

        return $this->created($ticket, 'Ticket created successfully');
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $ticket = Ticket::where('tenant_id', $this->getActiveTenantId($request))
            ->with(['creator:id,name', 'closer:id,name', 'parkingSlot:id,slot_number,floor,zone', 'vehicle', 'payments'])
            ->findOrFail($id);

        $pricing = null;
        if (in_array($ticket->status, [TicketStatus::ACTIVE, TicketStatus::LOST_TICKET, TicketStatus::OVERSTAY])) {
            $pricing = $this->pricingService->calculate($ticket, $this->getActiveTenant($request));
        }

        return $this->success([
            'ticket'  => $ticket,
            'pricing' => $pricing,
        ]);
    }

    public function updateStatus(int $id, UpdateTicketStatusRequest $request): JsonResponse
    {
        $validated     = $request->validated();
        $ticket        = Ticket::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);
        $currentStatus = $ticket->status->value;
        $newStatus     = $validated['status'];

        $allowedTransitions = [
            TicketStatus::ACTIVE->value     => [TicketStatus::COMPLETED->value, TicketStatus::CANCELLED->value, TicketStatus::LOST_TICKET->value, TicketStatus::DISPUTED->value],
            TicketStatus::LOST_TICKET->value=> [TicketStatus::COMPLETED->value, TicketStatus::CANCELLED->value],
            TicketStatus::OVERSTAY->value   => [TicketStatus::COMPLETED->value, TicketStatus::CANCELLED->value],
            TicketStatus::COMPLETED->value  => [TicketStatus::CLOSED->value],
            TicketStatus::DISPUTED->value   => [TicketStatus::COMPLETED->value, TicketStatus::CANCELLED->value],
        ];

        $allowed = $allowedTransitions[$currentStatus] ?? [];
        if (! in_array($newStatus, $allowed)) {
            return $this->error(
                "Cannot change status from '{$currentStatus}' to '{$newStatus}'.",
                409,
                'TICKET_003'
            );
        }

        DB::transaction(function () use ($ticket, $newStatus, $validated, $request) {
            $updateData = ['status' => $newStatus];

            if ($newStatus === TicketStatus::CLOSED->value || $newStatus === TicketStatus::CANCELLED->value) {
                $updateData['exit_at']    = now();
                $updateData['closed_by']  = $request->user()->id;

                if ($ticket->entry_at) {
                    $updateData['duration_minutes'] = (int) $ticket->entry_at->diffInMinutes(now());
                }

                if ($ticket->parking_slot_id) {
                    ParkingSlot::where('id', $ticket->parking_slot_id)->update([
                        'status'           => SlotStatus::AVAILABLE,
                        'current_ticket_id'=> null,
                    ]);
                }
            }

            if ($newStatus === TicketStatus::COMPLETED->value) {
                $updateData['exit_at']   = now();
                $updateData['closed_by'] = $request->user()->id;
                if ($ticket->entry_at) {
                    $updateData['duration_minutes'] = (int) $ticket->entry_at->diffInMinutes(now());
                }
            }

            if (isset($validated['notes'])) {
                $updateData['notes'] = $validated['notes'];
            }

            $ticket->update($updateData);
        });

        $ticket->refresh();
        $ticket->load(['creator:id,name', 'parkingSlot:id,slot_number,floor,zone']);

        return $this->success($ticket, 'Ticket status updated');
    }

    public function calculatePrice(int $id, Request $request): JsonResponse
    {
        $ticket = Ticket::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);

        if (! in_array($ticket->status, [TicketStatus::ACTIVE, TicketStatus::LOST_TICKET, TicketStatus::OVERSTAY])) {
            return $this->error('Cannot calculate price for a ' . $ticket->status->value . ' ticket.', 400);
        }

        $pricing = $this->pricingService->calculate($ticket, $this->getActiveTenant($request));

        return $this->success($pricing);
    }

    public function scan(ScanTicketRequest $request): JsonResponse
    {
        $ticket = Ticket::where('tenant_id', $this->getActiveTenantId($request))
            ->where('ticket_number', $request->validated('ticket_number'))
            ->with(['creator:id,name', 'parkingSlot:id,slot_number,floor,zone', 'vehicle'])
            ->first();

        if (! $ticket) {
            return $this->error('Ticket not found.', 404, 'TICKET_001');
        }

        $pricing = null;
        if (in_array($ticket->status, [TicketStatus::ACTIVE, TicketStatus::LOST_TICKET, TicketStatus::OVERSTAY])) {
            $pricing = $this->pricingService->calculate($ticket, $this->getActiveTenant($request));
        }

        return $this->success([
            'ticket'  => $ticket,
            'pricing' => $pricing,
        ]);
    }
}
