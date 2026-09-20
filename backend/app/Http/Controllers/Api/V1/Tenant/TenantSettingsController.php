<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\UpdateTenantSettingsRequest;
use App\Models\Tenant;
use App\Traits\ApiResponse;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantSettingsController extends Controller
{
    use ApiResponse, ResolvesActiveTenant;

    public function show(Request $request): JsonResponse
    {
        $tenant = Tenant::findOrFail($this->getActiveTenantId($request));

        return $this->success($tenant->settings ?? [], 'Settings retrieved');
    }

    public function update(UpdateTenantSettingsRequest $request): JsonResponse
    {
        $tenant = Tenant::findOrFail($this->getActiveTenantId($request));

        $validated = $request->validated();
        $current   = $tenant->settings ?? [];

        $merged = $this->deepMerge($current, $validated);

        $tenant->update(['settings' => $merged]);

        return $this->success($tenant->fresh()->settings ?? [], 'Settings updated');
    }

    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function deepMerge(array $base, array $override): array
    {
        foreach ($override as $key => $value) {
            $baseIsAssoc = isset($base[$key]) && is_array($base[$key]) && ! array_is_list($base[$key]);
            if (is_array($value) && $baseIsAssoc && ! array_is_list($value)) {
                $base[$key] = $this->deepMerge($base[$key], $value);
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }
}
