<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        // Dashboard
        // Create Permissions
        // Dashboard
        Permission::firstOrCreate(['name' => 'view dashboard']);

        // Customers
        Permission::firstOrCreate(['name' => 'view customers']);
        Permission::firstOrCreate(['name' => 'create customers']);
        Permission::firstOrCreate(['name' => 'edit customers']);
        Permission::firstOrCreate(['name' => 'delete customers']);
        Permission::firstOrCreate(['name' => 'export customers']);

        // Marketers
        Permission::firstOrCreate(['name' => 'view marketers']);
        Permission::firstOrCreate(['name' => 'create marketers']);
        Permission::firstOrCreate(['name' => 'edit marketers']);
        Permission::firstOrCreate(['name' => 'delete marketers']);
        Permission::firstOrCreate(['name' => 'approve marketers']);

        // Withdrawals
        Permission::firstOrCreate(['name' => 'view withdrawals']);
        Permission::firstOrCreate(['name' => 'approve withdrawals']);
        Permission::firstOrCreate(['name' => 'reject withdrawals']);

        // Reports
        Permission::firstOrCreate(['name' => 'view reports']);
        Permission::firstOrCreate(['name' => 'export reports']);

        // Settings & System
        Permission::firstOrCreate(['name' => 'manage settings']);
        Permission::firstOrCreate(['name' => 'manage roles']);
        Permission::firstOrCreate(['name' => 'manage staff']);

        // Create Roles and Assign Permissions

        // Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        // Super Admin gets all permissions via Gate::before rule or just implicitly

        // Admin
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'view dashboard',
            'view customers',
            'create customers',
            'edit customers',
            'view marketers',
            'edit marketers',
            'approve marketers',
            'view withdrawals',
            'approve withdrawals',
            'reject withdrawals',
            'view reports',
            'export reports',
            'manage roles',
            'manage staff',
        ]);

        // Affiliate (Marketer)
        $affiliate = Role::firstOrCreate(['name' => 'affiliate']);
        $affiliate->givePermissionTo([
            'view dashboard',
        ]);

        // Employee (Standard Staff)
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->givePermissionTo([
            'view dashboard',
            'view customers',
            'view marketers',
        ]);
    }
}
