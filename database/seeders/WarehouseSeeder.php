<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Main Warehouse',
                'code' => 'WH001',
                'type' => 'main',
                'is_default' => true,
                'is_active' => true,
                'address' => '123 Main Street, City Center',
                'phone' => '+1 (555) 123-4567',
                'email' => 'warehouse@company.com',
                'contact_person' => 'John Doe',
                'capacity' => 10000,
            ],
            [
                'name' => 'East Branch',
                'code' => 'WH002',
                'type' => 'branch',
                'is_default' => false,
                'is_active' => true,
                'address' => '456 East Avenue, East District',
                'phone' => '+1 (555) 987-6543',
                'email' => 'eastbranch@company.com',
                'contact_person' => 'Jane Smith',
                'capacity' => 5000,
            ],
            [
                'name' => 'North Store',
                'code' => 'WH003',
                'type' => 'store',
                'is_default' => false,
                'is_active' => true,
                'address' => '789 North Road, North Area',
                'phone' => '+1 (555) 456-7890',
                'email' => 'northstore@company.com',
                'contact_person' => 'Bob Johnson',
                'capacity' => 2000,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::updateOrCreate(['code' => $warehouse['code']], $warehouse);
        }

        $this->command->info('Warehouses created successfully!');
    }
}
