<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Global Electronics Ltd.',
                'code' => 'SUP001',
                'contact_person' => 'David Chen',
                'email' => 'david@globalelectronics.com',
                'phone' => '012111222',
                'address' => 'Tech Park, Singapore',
                'is_active' => true,
            ],
            [
                'name' => 'Phnom Penh Trading Co.',
                'code' => 'SUP002',
                'contact_person' => 'Sok Dara',
                'email' => 'sok@pptrading.com',
                'phone' => '012333444',
                'address' => 'Street 123, Phnom Penh',
                'is_active' => true,
            ],
            [
                'name' => 'Quality Goods Import Export',
                'code' => 'SUP003',
                'contact_person' => 'Ly Heng',
                'email' => 'ly@qualitygoods.com',
                'phone' => '012555666',
                'address' => 'Import Zone, Sihanoukville',
                'is_active' => true,
            ],
            [
                'name' => 'Bangkok Wholesale Corp.',
                'code' => 'SUP004',
                'contact_person' => 'Somsak Thai',
                'email' => 'somsak@bangkokwholesale.com',
                'phone' => '+6621234567',
                'address' => 'Bangkok, Thailand',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $this->command->info('Suppliers created successfully!');
    }
}