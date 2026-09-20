<?php

declare(strict_types=1);

namespace App\Enums;

enum SlotStatus: string
{
    case AVAILABLE = 'available';
    case OCCUPIED = 'occupied';
    case RESERVED = 'reserved';
    case MAINTENANCE = 'maintenance';
    case OUT_OF_SERVICE = 'out_of_service';
}
