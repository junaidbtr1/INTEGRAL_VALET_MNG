<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price_monthly' => $this->price_monthly,
            'price_yearly' => $this->price_yearly,
            'currency' => $this->currency,
            'max_slots' => $this->max_slots,
            'max_staff_users' => $this->max_staff_users,
            'max_tickets_per_day' => $this->max_tickets_per_day,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'tenants_count' => $this->whenCounted('tenants'),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
