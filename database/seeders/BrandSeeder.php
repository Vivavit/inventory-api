<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'Apple', 'Samsung', 'Sony', 'Nike', 'Adidas',
            'Dell', 'HP', 'Canon', 'Nikon', 'LG',
            'Panasonic', 'Microsoft', 'Intel', 'AMD', 'ASUS',
            'Lenovo', 'Acer', 'Philips', 'Bosch', 'Siemens',
        ];

        foreach ($brands as $index => $brandName) {
            Brand::updateOrCreate(
                ['slug' => Str::slug($brandName)], // unique key to match on
                [
                    'name'        => $brandName,
                    'description' => "Quality products from {$brandName}",
                    'website'     => "https://www.{$brandName}.com",
                    'is_active'   => true,
                    'sort_order'  => $index,
                ]
            );
        }

        $this->command->info('Brands created successfully!');
    }
}