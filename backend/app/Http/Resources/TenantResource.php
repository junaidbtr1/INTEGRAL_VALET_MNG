<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'domain' => $this->domain,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'logo_url' => $this->logo_url,
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'accent_color' => $this->accent_color,
            'status' => $this->status,
            'owner_id' => $this->owner_id,
            'owner' => $this->whenLoaded('owner', fn () => [
                'id'    => $this->owner->id,
                'name'  => $this->owner->name,
                'email' => $this->owner->email,
            ]),
            'plan' => new PlanResource($this->whenLoaded('plan')),
            'features' => $this->features,
            'settings' => $this->settings,
            'trial_ends_at' => $this->trial_ends_at?->toISOString(),
            'subscription_ends_at' => $this->subscription_ends_at?->toISOString(),
            'users_count' => $this->whenCounted('users'),
            'tickets_count' => $this->whenCounted('tickets'),
            'parking_slots_count' => $this->whenCounted('parkingSlots'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
