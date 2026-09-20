<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SlotStatus;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParkingSlot extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'floor',
        'zone',
        'slot_number',
        'slot_type',
        'status',
        'vehicle_types_allowed',
        'is_covered',
        'current_ticket_id',
        'sort_order',
    ];

    protected $casts = [
        'status' => SlotStatus::class,
        'vehicle_types_allowed' => 'array',
        'is_covered' => 'boolean',
    ];

    public function currentTicket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'current_ticket_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === SlotStatus::AVAILABLE;
    }
}
