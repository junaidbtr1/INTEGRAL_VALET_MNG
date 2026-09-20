<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case DIGITAL = 'digital';
    case COUPON = 'coupon';
}
