<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Ticket;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $stats = [
            'total_tenants'          => Tenant::count(),
            'active_tenants'         => Tenant::where('status', 'active')->count(),
            'trial_tenants'          => Tenant::where('status', 'trial')->count(),
            'suspended_tenants'      => Tenant::where('status', 'suspended')->count(),
            'total_users'            => User::where('is_super_admin', false)->count(),
            'total_tickets_today'    => Ticket::whereDate('created_at', today())->count(),
            'total_tickets_month'    => Ticket::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
            'total_revenue_today'    => (int) Payment::where('status', PaymentStatus::COMPLETED)->whereDate('created_at', today())->sum('amount'),
            'total_revenue_month'    => (int) Payment::where('status', PaymentStatus::COMPLETED)->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount'),
            'total_plans'            => Plan::where('is_active', true)->count(),
            'new_tenants_this_month' => Tenant::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recentTenants = Tenant::with('plan')
            ->withCount(['users', 'tickets'])
            ->latest()
            ->take(5)
            ->get();

        return $this->success([
            'stats' => $stats,
            'recent_tenants' => $recentTenants,
        ]);
    }
}
