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

        // Permission Groups Definition
        $groups = [
            'Dashboard' => ['view dashboard'],
            'Sales' => ['view customers', 'create customers', 'edit customers', 'delete customers', 'export customers'],
            'Marketers' => ['view marketers', 'create marketers', 'edit marketers', 'delete marketers', 'approve marketers'],
            'Withdrawals' => [
                'view withdrawals',
                'approve withdrawals',
                'finance approve withdrawals',
                'admin approve withdrawals',
                'reject withdrawals'
            ],
            'Commissions' => ['view commissions', 'manage commissions'],
            'Reports' => ['view reports', 'export reports'],
            'Users Management' => ['manage staff', 'manage roles'],
            'Settings' => ['manage settings'],
            'Widgets' => [
                'view sales widget',
                'view performance widget',
                'view withdrawals widget',
                'view marketers widget',
                'view sectors widget',
                'view recent leads widget'
            ],
        ];

        foreach ($groups as $group => $permissions) {
            foreach ($permissions as $permissionName) {
                Permission::firstOrCreate(['name' => $permissionName])
                    ->update(['group' => $group]);
            }
        }

        // Create Roles and Assign Permissions
        // Super Admin
        Role::firstOrCreate(['name' => 'super-admin']);

        // Admin
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::whereNotIn('name', ['super-admin'])->get()); // Give all

        // Affiliate (Marketer)
        $affiliate = Role::firstOrCreate(['name' => 'affiliate']);
        $affiliate->syncPermissions(['view dashboard']);

        // Employee (Standard Staff)
        $employee = Role::firstOrCreate(['name' => 'employee']);
        $employee->syncPermissions([
            'view dashboard',
            'view customers',
            'view marketers',
        ]);
    }
}
