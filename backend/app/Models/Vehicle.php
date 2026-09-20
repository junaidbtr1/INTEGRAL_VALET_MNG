<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VehicleType;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'plate_number',
        'vehicle_type',
        'color',
        'make',
        'model',
        'owner_name',
        'owner_phone',
        'is_vip',
        'is_blacklisted',
        'blacklist_reason',
        'photo_url',
        'notes',
    ];

    protected $casts = [
        'vehicle_type' => VehicleType::class,
        'is_vip' => 'boolean',
        'is_blacklisted' => 'boolean',
        'visit_count' => 'integer',
        'last_visit_at' => 'datetime',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
