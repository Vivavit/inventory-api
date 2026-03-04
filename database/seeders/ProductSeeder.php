<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\InventoryLocation;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands = Brand::all();
        $warehouses = Warehouse::all();

        if ($categories->isEmpty() || $brands->isEmpty() || $warehouses->isEmpty()) {
            $this->command->error('Cannot seed products. Please seed categories, brands, and warehouses first.');
            return;
        }

        // Sample products for inventory system
        $sampleProducts = [
            [
                'name' => 'Laptop Dell XPS 15',
                'description' => 'High-performance laptop for professionals',
                'price' => 1499.99,
                'cost_price' => 1200.00,
            ],
            [
                'name' => 'Wireless Mouse Logitech MX',
                'description' => 'Ergonomic wireless mouse',
                'price' => 89.99,
                'cost_price' => 60.00,
            ],
            [
                'name' => 'External SSD 1TB Samsung',
                'description' => 'Fast portable storage',
                'price' => 129.99,
                'cost_price' => 95.00,
            ],
            [
                'name' => 'Mechanical Keyboard Cherry MX',
                'description' => 'Gaming mechanical keyboard',
                'price' => 149.99,
                'cost_price' => 110.00,
            ],
            [
                'name' => '27-inch Monitor 4K',
                'description' => 'Ultra HD computer monitor',
                'price' => 399.99,
                'cost_price' => 320.00,
            ],
        ];

        $this->command->info('Creating inventory products...');
        $bar = $this->command->getOutput()->createProgressBar(count($sampleProducts));

        foreach ($sampleProducts as $index => $productData) {
            // Generate unique slug
            $slug = Str::slug($productData['name']);
            $originalSlug = $slug;
            $counter = 1;
            
            // Make sure slug is unique
            while (Product::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            // Generate unique SKU
            $sku = 'SKU-' . strtoupper(Str::random(8));
            while (Product::where('sku', $sku)->exists()) {
                $sku = 'SKU-' . strtoupper(Str::random(8));
            }

            // Create or find product by name (or create if doesn't exist)
            $product = Product::firstOrCreate(
                ['name' => $productData['name']], // Find by name
                [
                    'category_id' => $categories->random()->id,
                    'brand_id' => $brands->random()->id,
                    'slug' => $slug, // Use the unique slug we generated
                    'sku' => $sku, // Use the unique SKU we generated
                    'short_description' => substr($productData['description'], 0, 100),
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'compare_price' => $productData['price'] * 1.1, // 10% higher
                    'cost_price' => $productData['cost_price'],
                    'default_low_stock_threshold' => rand(5, 20),
                    'manage_stock' => true,
                    'is_active' => true,
                    'is_featured' => $index < 2,
                    'has_variants' => $index % 3 == 0,
                    'weight' => rand(0.5, 5.0),
                ]
            );

            // Only create related data if product was just created
            if ($product->wasRecentlyCreated) {
                // Create product image
                ProductImage::firstOrCreate(
                    ['product_id' => $product->id, 'is_primary' => true],
                    [
                        'image_path' => 'products/default.jpg',
                        'alt_text' => $product->name,
                        'is_primary' => true,
                        'sort_order' => 0,
                    ]
                );

                // Create variants if product has variants
                if ($product->has_variants) {
                    $colors = ['Black', 'Silver', 'White'];
                    foreach ($colors as $colorIndex => $color) {
                        // Generate unique variant SKU
                        $variantSku = 'VAR-' . strtoupper(Str::random(8));
                        while (ProductVariant::where('sku', $variantSku)->exists()) {
                            $variantSku = 'VAR-' . strtoupper(Str::random(8));
                        }

                        $variant = ProductVariant::firstOrCreate(
                            ['product_id' => $product->id, 'name' => $product->name . ' - ' . $color],
                            [
                                'sku' => $variantSku,
                                'name' => $product->name . ' - ' . $color,
                                'options' => json_encode(['color' => $color]),
                                'price' => $product->price + ($colorIndex * 10),
                                'compare_price' => ($product->price + ($colorIndex * 10)) * 1.1,
                                'is_active' => true,
                                'sort_order' => $colorIndex,
                            ]
                        );

                        // Add stock to warehouses for variant
                        foreach ($warehouses as $warehouse) {
                            InventoryLocation::firstOrCreate(
                                [
                                    'product_id' => $product->id,
                                    'product_variant_id' => $variant->id,
                                    'warehouse_id' => $warehouse->id,
                                ],
                                [
                                    'quantity' => rand(10, 100),
                                    'reserved_quantity' => 0,
                                    'location_code' => 'A' . ($index + 1) . '-' . $colorIndex,
                                ]
                            );
                        }
                    }
                } else {
                    // Add stock to warehouses for product without variants
                    foreach ($warehouses as $warehouse) {
                        InventoryLocation::firstOrCreate(
                            [
                                'product_id' => $product->id,
                                'warehouse_id' => $warehouse->id,
                                'product_variant_id' => null,
                            ],
                            [
                                'quantity' => rand(20, 200),
                                'reserved_quantity' => 0,
                                'location_code' => 'B' . ($index + 1),
                            ]
                        );
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Inventory products with stock created successfully!');
    }
}