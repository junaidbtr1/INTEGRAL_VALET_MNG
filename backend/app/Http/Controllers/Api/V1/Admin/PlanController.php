<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StorePlanRequest;
use App\Http\Requests\Api\Admin\UpdatePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = Plan::withCount('tenants')
            ->orderBy('sort_order')
            ->get();

        return $this->success(PlanResource::collection($plans), 'Plans retrieved successfully');
    }

    public function store(StorePlanRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['slug'] = Str::slug($validated['name']);

        $plan = Plan::create($validated);

        return $this->created(new PlanResource($plan), 'Plan created successfully');
    }

    public function show(Plan $plan): JsonResponse
    {
        $plan->loadCount('tenants');

        return $this->success(new PlanResource($plan));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse
    {
        $plan->update($request->validated());

        return $this->success(new PlanResource($plan), 'Plan updated successfully');
    }

    public function destroy(Plan $plan): JsonResponse
    {
        if ($plan->tenants()->exists()) {
            return $this->error('Cannot delete plan with active tenants', 409, 'PLAN_001');
        }

        $plan->delete();

        return $this->noContent('Plan deleted successfully');
    }
}
