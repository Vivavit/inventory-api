<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\InventoryLocation;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $brands     = Brand::all();
        $warehouses = Warehouse::all();

        if ($categories->isEmpty() || $brands->isEmpty() || $warehouses->isEmpty()) {
            $this->command->error('Cannot seed products. Please seed categories, brands, and warehouses first.');
            return;
        }

        if (Product::count() > 0) {
            $this->command->info('Products already seeded, skipping.');
            return;
        }

        $sampleProducts = [
            [
                'name'        => 'Laptop Dell XPS 15',
                'description' => 'High-performance laptop for professionals',
                'price'       => 1499.99,
                'cost_price'  => 1200.00,
            ],
            [
                'name'        => 'Wireless Mouse Logitech MX',
                'description' => 'Ergonomic wireless mouse',
                'price'       => 89.99,
                'cost_price'  => 60.00,
            ],
            [
                'name'        => 'External SSD 1TB Samsung',
                'description' => 'Fast portable storage',
                'price'       => 129.99,
                'cost_price'  => 95.00,
            ],
            [
                'name'        => 'Mechanical Keyboard Cherry MX',
                'description' => 'Gaming mechanical keyboard',
                'price'       => 149.99,
                'cost_price'  => 110.00,
            ],
            [
                'name'        => '27-inch Monitor 4K',
                'description' => 'Ultra HD computer monitor',
                'price'       => 399.99,
                'cost_price'  => 320.00,
            ],
        ];

        $this->command->info('Creating inventory products...');
        $bar = $this->command->getOutput()->createProgressBar(count($sampleProducts));

        foreach ($sampleProducts as $productIndex => $productData) {

            // --- Slug: only generate a new unique one when the product doesn't exist yet ---
            $existingProduct = Product::where('name', $productData['name'])->first();

            if ($existingProduct) {
                $slug = $existingProduct->slug;
                $sku  = $existingProduct->sku;
            } else {
                $slug         = Str::slug($productData['name']);
                $originalSlug = $slug;
                $counter      = 1;
                while (Product::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $counter++;
                }

                $sku = 'SKU-' . strtoupper(Str::random(8));
                while (Product::where('sku', $sku)->exists()) {
                    $sku = 'SKU-' . strtoupper(Str::random(8));
                }
            }

            $product = Product::updateOrCreate(
                ['name' => $productData['name']],
                [
                    'category_id'                 => $categories->random()->id,
                    'brand_id'                    => $brands->random()->id,
                    'slug'                        => $slug,
                    'sku'                         => $sku,
                    'short_description'           => substr($productData['description'], 0, 100),
                    'description'                 => $productData['description'],
                    'price'                       => $productData['price'],
                    'compare_price'               => $productData['price'] * 1.1,
                    'cost_price'                  => $productData['cost_price'],
                    'default_low_stock_threshold' => rand(5, 20),
                    'manage_stock'                => true,
                    'is_active'                   => true,
                    'is_featured'                 => $productIndex < 2,
                    'has_variants'                => $productIndex % 3 == 0,
                    'weight'                      => rand(1, 5),  // cast-safe integer range
                ]
            );

            // --- Images: delete old ones and recreate (idempotent) ---
            $product->images()->delete();

            $imageFiles = ['img_1.jpg', 'img_2.png', 'img_3.png', 'img_4.png', 'img_5.jpg', 'img_6.jpg'];
            foreach ($imageFiles as $imgIndex => $imageFile) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'products/' . $imageFile,
                    'alt_text'   => $product->name . ' - Image ' . ($imgIndex + 1),
                    'is_primary' => $imgIndex === 0,
                    'sort_order' => $imgIndex,
                ]);
            }

            // --- Variants / Inventory ---
            if ($product->has_variants) {
                $colors = ['Black', 'Silver', 'White'];

                foreach ($colors as $colorIndex => $color) {
                    $variantName = $product->name . ' - ' . $color;

                    // Find existing variant or generate a fresh unique SKU
                    $existingVariant = ProductVariant::where('product_id', $product->id)
                        ->where('name', $variantName)
                        ->first();

                    $variantSku = $existingVariant
                        ? $existingVariant->sku
                        : $this->uniqueVariantSku();

                    $variant = ProductVariant::updateOrCreate(
                        ['product_id' => $product->id, 'name' => $variantName],
                        [
                            'sku'           => $variantSku,
                            'options'       => json_encode(['color' => $color]),
                            'price'         => $product->price + ($colorIndex * 10),
                            'compare_price' => ($product->price + ($colorIndex * 10)) * 1.1,
                            'is_active'     => true,
                            'sort_order'    => $colorIndex,
                        ]
                    );

                    foreach ($warehouses as $warehouse) {
                        InventoryLocation::firstOrCreate(
                            [
                                'product_id'         => $product->id,
                                'product_variant_id' => $variant->id,
                                'warehouse_id'        => $warehouse->id,
                            ],
                            [
                                'quantity'          => rand(10, 100),
                                'reserved_quantity' => 0,
                                'location_code'     => 'A' . ($productIndex + 1) . '-' . $colorIndex,
                            ]
                        );
                    }
                }
            } else {
                foreach ($warehouses as $warehouse) {
                    InventoryLocation::firstOrCreate(
                        [
                            'product_id'         => $product->id,
                            'warehouse_id'        => $warehouse->id,
                            'product_variant_id' => null,
                        ],
                        [
                            'quantity'          => rand(20, 200),
                            'reserved_quantity' => 0,
                            'location_code'     => 'B' . ($productIndex + 1),
                        ]
                    );
                }
            }

            // --- Sync WarehouseProduct totals ---
            foreach ($warehouses as $warehouse) {
                $totalQty = InventoryLocation::where('product_id', $product->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->sum('quantity');

                WarehouseProduct::updateOrCreate(
                    ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                    ['quantity'     => $totalQty]
                );
            }

            $bar->advance();
        }

        $bar->finish();
        $this->command->newLine();
        $this->command->info('Inventory products with stock created successfully!');
    }

    private function uniqueVariantSku(): string
    {
        do {
            $sku = 'VAR-' . strtoupper(Str::random(8));
        } while (ProductVariant::where('sku', $sku)->exists());

        return $sku;
    }
}