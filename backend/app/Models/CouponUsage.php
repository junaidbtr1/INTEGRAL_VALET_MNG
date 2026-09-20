<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    use HasTenant;

    public $timestamps = false;

    protected $fillable = [
        'coupon_id',
        'ticket_id',
        'user_id',
        'tenant_id',
        'discount_amount',
        'used_at',
    ];

    protected $casts = [
        'discount_amount' => 'integer',
        'used_at' => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
