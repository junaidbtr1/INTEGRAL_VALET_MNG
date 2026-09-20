<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create all permissions
        $permissions = [
            // Tickets
            'tickets' => ['tickets.view', 'tickets.create', 'tickets.update', 'tickets.delete', 'tickets.close', 'tickets.cancel', 'tickets.export', 'tickets.bulk_create', 'tickets.reassign'],
            // Payments
            'payments' => ['payments.view', 'payments.create', 'payments.refund', 'payments.export', 'payments.reconcile', 'payments.void'],
            // Users
            'users' => ['users.view', 'users.create', 'users.update', 'users.delete', 'users.activate', 'users.deactivate', 'users.reset_password', 'users.assign_role'],
            // Vehicles
            'vehicles' => ['vehicles.view', 'vehicles.create', 'vehicles.update', 'vehicles.delete', 'vehicles.search'],
            // Parking Slots
            'slots' => ['slots.view', 'slots.create', 'slots.update', 'slots.delete', 'slots.reserve', 'slots.release'],
            // Coupons
            'coupons' => ['coupons.view', 'coupons.create', 'coupons.update', 'coupons.delete', 'coupons.activate', 'coupons.deactivate'],
            // Reports
            'reports' => ['reports.view', 'reports.export', 'reports.schedule', 'reports.revenue', 'reports.occupancy', 'reports.staff'],
            // Settings
            'settings' => ['settings.view', 'settings.update', 'settings.branding', 'settings.features', 'settings.billing', 'settings.notifications'],
            // Audit Logs
            'audit_logs' => ['audit_logs.view', 'audit_logs.export'],
            // Webhooks
            'webhooks' => ['webhooks.view', 'webhooks.create', 'webhooks.update', 'webhooks.delete'],
        ];

        foreach ($permissions as $group => $perms) {
            foreach ($perms as $perm) {
                Permission::firstOrCreate(
                    ['name' => $perm, 'guard_name' => 'sanctum'],
                    ['group' => $group]
                );
            }
        }

        // Create roles (without team — these are templates)
        $allPermissions = Permission::where('guard_name', 'sanctum')->get();

        // Tenant Admin — gets all permissions
        $tenantAdmin = Role::firstOrCreate(
            ['name' => 'tenant_admin', 'guard_name' => 'sanctum'],
            ['description' => 'Full access within their tenant']
        );
        $tenantAdmin->syncPermissions($allPermissions);

        // Supervisor
        $supervisor = Role::firstOrCreate(
            ['name' => 'supervisor', 'guard_name' => 'sanctum'],
            ['description' => 'Operational oversight']
        );
        $supervisor->syncPermissions([
            'tickets.view', 'tickets.create', 'tickets.update', 'tickets.close',
            'tickets.cancel', 'tickets.export', 'tickets.reassign',
            'payments.view',
            'users.view',
            'vehicles.view', 'vehicles.create', 'vehicles.update', 'vehicles.search',
            'slots.view', 'slots.update', 'slots.reserve', 'slots.release',
            'coupons.view',
            'reports.view', 'reports.revenue', 'reports.occupancy', 'reports.staff',
        ]);

        // Valet Staff
        $valetStaff = Role::firstOrCreate(
            ['name' => 'valet_staff', 'guard_name' => 'sanctum'],
            ['description' => 'Parks and retrieves vehicles']
        );
        $valetStaff->syncPermissions([
            'tickets.view', 'tickets.create', 'tickets.update', 'tickets.close',
            'vehicles.view', 'vehicles.create', 'vehicles.search',
            'slots.view',
        ]);

        // Cashier
        $cashier = Role::firstOrCreate(
            ['name' => 'cashier', 'guard_name' => 'sanctum'],
            ['description' => 'Handles payments']
        );
        $cashier->syncPermissions([
            'tickets.view', 'tickets.close',
            'payments.view', 'payments.create',
            'vehicles.view',
            'slots.view',
            'coupons.view',
        ]);

        // Viewer
        $viewer = Role::firstOrCreate(
            ['name' => 'viewer', 'guard_name' => 'sanctum'],
            ['description' => 'Read-only dashboard access']
        );
        $viewer->syncPermissions([
            'tickets.view',
            'payments.view',
            'slots.view',
            'reports.view',
        ]);
    }
}
