<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tenant\StoreCouponRequest;
use App\Http\Requests\Api\Tenant\UpdateCouponRequest;
use App\Http\Requests\Api\Tenant\ValidateCouponRequest;
use App\Models\Coupon;
use App\Traits\ResolvesActiveTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    use ResolvesActiveTenant;

    public function index(Request $request): JsonResponse
    {
        $query = Coupon::where('tenant_id', $this->getActiveTenantId($request));

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $query->where('code', 'like', '%' . strtoupper($request->input('search')) . '%');
        }

        $coupons = $query->latest()->get();

        return $this->success($coupons, 'Coupons retrieved successfully');
    }

    public function store(StoreCouponRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tenantId  = $this->getActiveTenantId($request);

        $coupon = Coupon::create([
            'tenant_id'                => $tenantId,
            'code'                     => $validated['code'],
            'type'                     => $validated['type'],
            'value'                    => $validated['value'],
            'min_amount'               => $validated['min_amount'] ?? 0,
            'max_discount'             => $validated['max_discount'] ?? null,
            'usage_limit'              => $validated['usage_limit'] ?? null,
            'per_user_limit'           => $validated['per_user_limit'] ?? 1,
            'applicable_vehicle_types' => $validated['applicable_vehicle_types'] ?? null,
            'valid_from'               => $validated['valid_from'] ?? null,
            'valid_until'              => $validated['valid_until'] ?? null,
            'description'              => $validated['description'] ?? null,
            'is_active'                => true,
        ]);

        return $this->created($coupon, 'Coupon created successfully');
    }

    public function update(int $id, UpdateCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);
        $coupon->update($request->validated());

        return $this->success($coupon, 'Coupon updated successfully');
    }

    public function destroy(int $id, Request $request): JsonResponse
    {
        $coupon = Coupon::where('tenant_id', $this->getActiveTenantId($request))->findOrFail($id);
        $coupon->delete();

        return $this->noContent();
    }

    public function validate_code(ValidateCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::where('tenant_id', $this->getActiveTenantId($request))
            ->where('code', strtoupper($request->validated('code')))
            ->where('is_active', true)
            ->first();

        if (! $coupon) {
            return $this->error('Invalid or inactive coupon code.', 404, 'PAYMENT_003');
        }

        if ($coupon->valid_from && now()->lt($coupon->valid_from)) {
            return $this->error('This coupon is not yet valid.', 400, 'PAYMENT_003');
        }

        if ($coupon->valid_until && now()->gt($coupon->valid_until)) {
            return $this->error('This coupon has expired.', 400, 'PAYMENT_004');
        }

        if ($coupon->usage_limit && $coupon->times_used >= $coupon->usage_limit) {
            return $this->error('This coupon has reached its usage limit.', 400, 'PAYMENT_005');
        }

        return $this->success($coupon, 'Coupon is valid');
    }
}
