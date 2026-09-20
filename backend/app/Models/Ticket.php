<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketStatus;
use App\Enums\VehicleType;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'ticket_number',
        'vehicle_id',
        'parking_slot_id',
        'created_by',
        'vehicle_plate',
        'vehicle_type',
        'vehicle_color',
        'vehicle_make',
        'status',
        'entry_at',
        'exit_at',
        'duration_minutes',
        'base_amount',
        'tax_amount',
        'discount_amount',
        'surcharge_amount',
        'total_amount',
        'qr_code_url',
        'barcode_url',
        'notes',
        'closed_by',
    ];

    protected $casts = [
        'status' => TicketStatus::class,
        'vehicle_type' => VehicleType::class,
        'entry_at' => 'datetime',
        'exit_at' => 'datetime',
        'base_amount' => 'integer',
        'tax_amount' => 'integer',
        'discount_amount' => 'integer',
        'surcharge_amount' => 'integer',
        'total_amount' => 'integer',
    ];

    // Relationships
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function parkingSlot(): BelongsTo
    {
        return $this->belongsTo(ParkingSlot::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
