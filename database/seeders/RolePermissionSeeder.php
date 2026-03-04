<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cache first
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // --- PERMISSIONS ---
        // web guard (used by Laravel web/admin panel)
        $webPermissions = [
            'view-dashboard', 'view-products', 'manage-products',
            'view-categories', 'manage-categories',
            'view-brands', 'manage-brands',
            'view-warehouses', 'manage-warehouses',
            'view-inventory', 'manage-inventory',
            'view-users', 'manage-users',
            'view-analytics', 'view-reports',
            'checkout',
        ];

        // sanctum guard (used by Flutter mobile API)
        $sanctumPermissions = [
            'view-dashboard', 'view-products', 'manage-products',
            'view-categories', 'manage-categories',
            'view-brands', 'manage-brands',
            'view-warehouses', 'manage-warehouses',
            'view-inventory', 'manage-inventory',
            'view-users', 'manage-users',
            'view-analytics', 'view-reports',
            'checkout',
        ];

        foreach ($webPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        foreach ($sanctumPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'sanctum']);
        }

        // --- ROLES ---
        $adminWeb     = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $staffWeb     = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $adminSanctum = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $staffSanctum = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'sanctum']);

        // Admin gets ALL permissions
        $adminWeb->syncPermissions(
            Permission::where('guard_name', 'web')->get()
        );
        $adminSanctum->syncPermissions(
            Permission::where('guard_name', 'sanctum')->get()
        );

        // Staff gets limited permissions
        $staffPerms = [
            'view-dashboard', 'view-products',
            'view-categories', 'view-brands',
            'view-warehouses', 'view-inventory',
            'view-analytics', 'checkout',
        ];

        $staffWeb->syncPermissions(
            Permission::whereIn('name', $staffPerms)->where('guard_name', 'web')->get()
        );
        $staffSanctum->syncPermissions(
            Permission::whereIn('name', $staffPerms)->where('guard_name', 'sanctum')->get()
        );

        $this->command->info('Roles & permissions created for web + sanctum guards');
    }
}