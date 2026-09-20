<?php

declare(strict_types=1);

namespace App\Enums;

enum CouponType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';
    case FREE_HOURS = 'free_hours';
    case FULL_WAIVER = 'full_waiver';
}
