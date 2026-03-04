<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    // Give admin (role_id=1) all web permissions
    DB::statement("INSERT INTO role_has_permissions (role_id,permission_id) SELECT 1,id FROM permissions WHERE guard_name='web' AND id NOT IN (SELECT permission_id FROM role_has_permissions WHERE role_id=1)");

    // Staff limited permissions for role_id=2
    $staffNames = ['view-dashboard', 'view-products', 'view-categories', 'view-brands', 'view-warehouses', 'view-inventory', 'view-analytics', 'checkout'];
    $staffPermIds = DB::table('permissions')->where('guard_name', 'web')->whereIn('name', $staffNames)->pluck('id')->toArray();
    foreach ($staffPermIds as $pid) {
        if (! DB::table('role_has_permissions')->where(['role_id' => 2, 'permission_id' => $pid])->exists()) {
            DB::table('role_has_permissions')->insert(['role_id' => 2, 'permission_id' => $pid]);
        }
    }

    // Assign roles to users: admin user id=1 -> role_id=1; staff users 2 and 3 -> role_id=2
    $assignments = [[1, 1], [2, 2], [2, 3]];
    foreach ($assignments as $pair) {
        $roleId = $pair[0];
        $userId = $pair[1];
        if (! DB::table('model_has_roles')->where(['role_id' => $roleId, 'model_id' => $userId])->exists()) {
            DB::table('model_has_roles')->insert(['role_id' => $roleId, 'model_type' => 'App\\Models\\User', 'model_id' => $userId]);
        }
    }

    DB::commit();
    echo "Assignments complete\n";
} catch (\Throwable $e) {
    DB::rollBack();
    echo 'Error: '.$e->getMessage()."\n";
}
