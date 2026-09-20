<?php

declare(strict_types=1);

namespace App\Enums;

enum VehicleType: string
{
    case MOTORCYCLE = 'motorcycle';
    case CAR = 'car';
    case SUV = 'suv';
    case VAN = 'van';
    case TRUCK = 'truck';
    case BUS = 'bus';
}
