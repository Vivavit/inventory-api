<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get existing products and customers
        $product = Product::inRandomOrder()->first();
        $customer = Customer::inRandomOrder()->first();

        // If no products or customers exist, create them
        if (! $product) {
            $product = Product::factory()->create();
        }
        if (! $customer) {
            $customer = Customer::factory()->create();
        }

        return [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'order_id' => null,
            'rating' => fake()->numberBetween(1, 5),
            'title' => fake()->sentence(5),
            'comment' => fake()->paragraph(3),
            'is_verified_purchase' => fake()->boolean(70),
            'is_approved' => fake()->boolean(80),
        ];
    }
}
