<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // --- Admin User ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@inventory.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('123456789'),
                'phone' => '0977141801',
                'user_type' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // --- Staff Users ---
        $staff1 = User::firstOrCreate(
            ['email' => 'staff1@inventory.com'],
            [
                'name' => 'Warehouse Staff 1',
                'password' => Hash::make('staff123'),
                'phone' => '0987654321',
                'user_type' => 'staff',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $staff2 = User::firstOrCreate(
            ['email' => 'staff2@inventory.com'],
            [
                'name' => 'Warehouse Staff 2',
                'password' => Hash::make('staff123'),
                'phone' => '0987654322',
                'user_type' => 'staff',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // --- Assign Warehouses ---
        $warehouses = Warehouse::all();

        if ($warehouses->isNotEmpty()) {
            // Detach first to avoid duplicate pivot errors on re-seed
            $admin->warehouses()->detach();
            $staff1->warehouses()->detach();
            $staff2->warehouses()->detach();

            // Admin gets all warehouses, first one is default
            foreach ($warehouses as $index => $warehouse) {
                $admin->warehouses()->attach($warehouse->id, [
                    'is_default' => $index === 0,
                ]);
            }

            // Staff 1 → Warehouse 1
            $wh1 = $warehouses->first();
            if ($wh1) {
                $staff1->warehouses()->attach($wh1->id, ['is_default' => true]);
            }

            // Staff 2 → Warehouse 2 (if exists)
            $wh2 = $warehouses->skip(1)->first();
            if ($wh2) {
                $staff2->warehouses()->attach($wh2->id, ['is_default' => true]);
            }
        }

        $this->command->info('Users created: admin@inventory.com / 123456789');
        $this->command->info('Staff: staff1@inventory.com / staff123');
        $this->command->info('Staff: staff2@inventory.com / staff123');
    }
}
