<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, HasTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'ticket_id',
        'processed_by',
        'receipt_number',
        'amount',
        'currency',
        'payment_method',
        'status',
        'refund_of',
        'refund_reason',
        'coupon_id',
        'coupon_discount_amount',
        'transaction_reference',
        'gateway_response',
        'notes',
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'amount' => 'integer',
        'coupon_discount_amount' => 'integer',
        'gateway_response' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function originalPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'refund_of');
    }
}
