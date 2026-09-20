<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\PlanController;
use App\Http\Controllers\Api\V1\Admin\TenantController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Tenant\CouponController;
use App\Http\Controllers\Api\V1\Tenant\ParkingSlotController;
use App\Http\Controllers\Api\V1\Tenant\PaymentController;
use App\Http\Controllers\Api\V1\Tenant\RoleController;
use App\Http\Controllers\Api\V1\Tenant\ShiftController;
use App\Http\Controllers\Api\V1\Tenant\ShiftReportController;
use App\Http\Controllers\Api\V1\Tenant\StaffController;
use App\Http\Controllers\Api\V1\Tenant\TenantDashboardController;
use App\Http\Controllers\Api\V1\Tenant\TenantReportController;
use App\Http\Controllers\Api\V1\Tenant\TenantSettingsController;
use App\Http\Controllers\Api\V1\Tenant\TicketController;
use App\Http\Controllers\Api\V1\Tenant\VehicleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

// Authenticated
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
    Route::post('/auth/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
    Route::put('/auth/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');

    // ─── Super Admin ───
    Route::prefix('admin')->middleware(\App\Http\Middleware\EnsureSuperAdmin::class)->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        Route::apiResource('/tenants', TenantController::class)->names('admin.tenants');
        Route::apiResource('/plans', PlanController::class)->names('admin.plans');
        Route::apiResource('/users', UserController::class)->names('admin.users');
    });

    // ─── Tenant ───
    Route::prefix('tenant')->middleware(\App\Http\Middleware\EnsureTenantAccess::class)->group(function () {

        // Dashboard
        Route::get('/dashboard', [TenantDashboardController::class, 'index'])->name('tenant.dashboard');

        // Reports
        Route::get('/reports', [TenantReportController::class, 'index'])->name('tenant.reports');
        Route::get('/reports/download', [TenantReportController::class, 'download'])->name('tenant.reports.download');
        Route::get('/shift-reports', [ShiftReportController::class, 'index'])->name('tenant.shift-reports');

        // Settings
        Route::get('/settings', [TenantSettingsController::class, 'show'])->name('tenant.settings.show');
        Route::patch('/settings', [TenantSettingsController::class, 'update'])->name('tenant.settings.update');

        // Roles & Permissions
        Route::get('/permissions', [RoleController::class, 'permissions'])->name('tenant.permissions');
        Route::apiResource('/roles', RoleController::class)->names('tenant.roles');

        // Shifts
        Route::apiResource('/shifts', ShiftController::class)->only(['index', 'store', 'update', 'destroy'])->names('tenant.shifts');

        // Staff
        Route::apiResource('/staff', StaffController::class)->names('tenant.staff');

        // Parking Slots
        Route::get('/slots/summary', [ParkingSlotController::class, 'summary'])->name('tenant.slots.summary');
        Route::post('/slots/bulk', [ParkingSlotController::class, 'bulkStore'])->name('tenant.slots.bulk');
        Route::patch('/slots/{id}/status', [ParkingSlotController::class, 'updateStatus'])->name('tenant.slots.status');
        Route::apiResource('/slots', ParkingSlotController::class)->names('tenant.slots');

        // Vehicles
        Route::get('/vehicles/search', [VehicleController::class, 'search'])->name('tenant.vehicles.search');
        Route::apiResource('/vehicles', VehicleController::class)->names('tenant.vehicles');

        // Tickets
        Route::post('/tickets/scan', [TicketController::class, 'scan'])->name('tenant.tickets.scan');
        Route::get('/tickets/{id}/price', [TicketController::class, 'calculatePrice'])->name('tenant.tickets.price');
        Route::patch('/tickets/{id}/status', [TicketController::class, 'updateStatus'])->name('tenant.tickets.status');
        Route::apiResource('/tickets', TicketController::class)->only(['index', 'store', 'show'])->names('tenant.tickets');

        // Payments
        Route::post('/payments/{id}/refund', [PaymentController::class, 'refund'])->name('tenant.payments.refund');
        Route::apiResource('/payments', PaymentController::class)->only(['index', 'store', 'show'])->names('tenant.payments');

        // Coupons
        Route::post('/coupons/validate', [CouponController::class, 'validate_code'])->name('tenant.coupons.validate');
        Route::apiResource('/coupons', CouponController::class)->names('tenant.coupons');
    });
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
    ]);
})->name('health');
