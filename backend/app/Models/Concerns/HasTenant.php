<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasTenant
{
    public static function bootHasTenant(): void
    {
        static::creating(function ($model) {
            if (! $model->tenant_id && auth()->check() && auth()->user()?->tenant_id) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });

        // Global scope: auto-filter by tenant for regular staff users.
        // Super admins and building owners are excluded — they operate across tenants.
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (! auth()->check()) {
                return;
            }

            $user = auth()->user();

            if (
                $user->is_super_admin
                || ($user->is_building_owner ?? false)
                || ! $user->tenant_id
            ) {
                return;
            }

            $builder->where($builder->getModel()->getTable() . '.tenant_id', $user->tenant_id);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }
}
