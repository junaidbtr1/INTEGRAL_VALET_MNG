<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case CREATED = 'created';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
    case LOST_TICKET = 'lost_ticket';
    case OVERSTAY = 'overstay';
    case DISPUTED = 'disputed';

    public function label(): string
    {
        return match ($this) {
            self::CREATED => 'Created',
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
            self::CLOSED => 'Closed',
            self::CANCELLED => 'Cancelled',
            self::LOST_TICKET => 'Lost Ticket',
            self::OVERSTAY => 'Overstay',
            self::DISPUTED => 'Disputed',
        };
    }
}
