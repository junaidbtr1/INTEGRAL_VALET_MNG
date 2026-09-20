<?php

declare(strict_types=1);

namespace App\Enums;

enum TenantStatus: string
{
    case TRIAL = 'trial';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
    case DELETED = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::TRIAL => 'Trial',
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::CANCELLED => 'Cancelled',
            self::DELETED => 'Deleted',
        };
    }
}
