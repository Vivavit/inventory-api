<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AssignAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Assign admin role (web + sanctum) to admin user
        $admin = User::where('email', 'admin@inventory.com')->first();
        if ($admin) {
            // syncRoles replaces existing roles - safe to re-run
            $admin->syncRoles(['admin']); // assigns web guard role
            // Also assign sanctum guard role manually
            $adminSanctumRole = \Spatie\Permission\Models\Role::where('name', 'admin')
                ->where('guard_name', 'sanctum')
                ->first();
            if ($adminSanctumRole) {
                \DB::table('model_has_roles')->insertOrIgnore([
                    'role_id'    => $adminSanctumRole->id,
                    'model_type' => 'App\Models\User',
                    'model_id'   => $admin->id,
                ]);
            }
            $this->command->info('Admin role assigned to admin@inventory.com');
        }

        // Assign staff role to all staff users
        $staffUsers = User::where('user_type', 'staff')->get();
        $staffSanctumRole = \Spatie\Permission\Models\Role::where('name', 'staff')
            ->where('guard_name', 'sanctum')
            ->first();

        foreach ($staffUsers as $user) {
            $user->syncRoles(['staff']);
            if ($staffSanctumRole) {
                \DB::table('model_has_roles')->insertOrIgnore([
                    'role_id'    => $staffSanctumRole->id,
                    'model_type' => 'App\Models\User',
                    'model_id'   => $user->id,
                ]);
            }
        }

        $this->command->info("Staff role assigned to {$staffUsers->count()} staff users");
    }
}