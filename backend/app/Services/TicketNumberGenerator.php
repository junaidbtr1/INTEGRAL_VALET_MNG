<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tenant;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketNumberGenerator
{
    public function generate(int $tenantId): string
    {
        $tenant = Tenant::findOrFail($tenantId);
        $prefix = $tenant->settings['ticket']['prefix'] ?? strtoupper(substr($tenant->name, 0, 3));
        $date = now()->format('ymd');

        // Atomic sequence: get today's max sequence for this tenant
        $lastTicket = Ticket::where('tenant_id', $tenantId)
            ->where('ticket_number', 'like', "{$prefix}-{$date}-%")
            ->orderByDesc('ticket_number')
            ->lockForUpdate()
            ->first();

        if ($lastTicket) {
            $lastSequence = (int) substr($lastTicket->ticket_number, -5);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s-%05d', $prefix, $date, $sequence);
    }
}
