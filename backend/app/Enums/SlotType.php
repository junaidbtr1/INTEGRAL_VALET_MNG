<?php

declare(strict_types=1);

namespace App\Enums;

enum SlotType: string
{
    case STANDARD = 'standard';
    case COMPACT = 'compact';
    case LARGE = 'large';
    case DISABLED = 'disabled';
    case EV_CHARGING = 'ev_charging';
    case VIP = 'vip';
    case RESERVED = 'reserved';
}
