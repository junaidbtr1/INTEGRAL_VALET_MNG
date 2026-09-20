<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TenantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Coupon;
use App\Models\ParkingSlot;
use App\Models\Payment;
use App\Models\Shift;
use App\Models\Ticket;
use App\Models\Vehicle;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'domain',
        'email',
        'phone',
        'address',
        'logo_url',
        'primary_color',
        'secondary_color',
        'accent_color',
        'status',
        'plan_id',
        'owner_id',
        'trial_ends_at',
        'subscription_ends_at',
        'features',
        'settings',
        'data',
    ];

    protected $casts = [
        'status' => TenantStatus::class,
        'features' => 'array',
        'settings' => 'array',
        'data' => 'array',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    // Relationships
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function parkingSlots(): HasMany
    {
        return $this->hasMany(ParkingSlot::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (Tenant $tenant): void {
            $id = $tenant->id;

            // Revoke all user tokens first, then soft-delete users
            User::where('tenant_id', $id)->each(function (User $user): void {
                $user->tokens()->delete();
                $user->delete();
            });

            // Soft-delete all tenant data
            Ticket::where('tenant_id', $id)->whereNull('deleted_at')->update(['deleted_at' => now()]);
            Payment::where('tenant_id', $id)->whereNull('deleted_at')->update(['deleted_at' => now()]);
            Vehicle::where('tenant_id', $id)->whereNull('deleted_at')->update(['deleted_at' => now()]);
            ParkingSlot::where('tenant_id', $id)->whereNull('deleted_at')->update(['deleted_at' => now()]);
            Coupon::where('tenant_id', $id)->whereNull('deleted_at')->update(['deleted_at' => now()]);
            Shift::where('tenant_id', $id)->whereNull('deleted_at')->update(['deleted_at' => now()]);
        });

        static::restoring(function (Tenant $tenant): void {
            $id = $tenant->id;

            User::onlyTrashed()->where('tenant_id', $id)->each(fn (User $u) => $u->restore());
            Ticket::onlyTrashed()->where('tenant_id', $id)->update(['deleted_at' => null]);
            Payment::onlyTrashed()->where('tenant_id', $id)->update(['deleted_at' => null]);
            Vehicle::onlyTrashed()->where('tenant_id', $id)->update(['deleted_at' => null]);
            ParkingSlot::onlyTrashed()->where('tenant_id', $id)->update(['deleted_at' => null]);
            Coupon::onlyTrashed()->where('tenant_id', $id)->update(['deleted_at' => null]);
            Shift::onlyTrashed()->where('tenant_id', $id)->update(['deleted_at' => null]);
        });
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === TenantStatus::ACTIVE;
    }

    public function isTrial(): bool
    {
        return $this->status === TenantStatus::TRIAL;
    }

    public function isSuspended(): bool
    {
        return $this->status === TenantStatus::SUSPENDED;
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }
}
