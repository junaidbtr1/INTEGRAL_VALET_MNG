<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasTenant, Notifiable, SoftDeletes;

    protected string $guard_name = 'sanctum';

    protected $fillable = [
        'tenant_id',
        'shift_id',
        'name',
        'email',
        'phone',
        'password',
        'is_super_admin',
        'is_building_owner',
        'is_active',
        'avatar_url',
        'timezone',
        'locale',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'force_password_change',
        'last_login_at',
        'last_login_ip',
        'failed_login_attempts',
        'locked_until',
        'password_changed_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
        'is_building_owner' => 'boolean',
        'is_active' => 'boolean',
        'two_factor_enabled' => 'boolean',
        'two_factor_recovery_codes' => 'array',
        'shift_id' => 'integer',
        'failed_login_attempts' => 'integer',
        'locked_until' => 'datetime',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'force_password_change' => 'boolean',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function ownedBuildings(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    public function processedPayments(): HasMany
    {
        return $this->hasMany(Payment::class, 'processed_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin;
    }

    public function isBuildingOwner(): bool
    {
        return $this->is_building_owner;
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }
}
