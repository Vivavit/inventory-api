<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Console\Command;

class PopulateOrderItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:populate-order-items';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate order items for existing orders without items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orders = Order::doesntHave('items')->get();

        if ($orders->isEmpty()) {
            $this->info('No orders without items found.');
            return;
        }

        $products = Product::all();

        if ($products->isEmpty()) {
            $this->error('No products found in database.');
            return;
        }

        $this->info("Processing " . $orders->count() . " orders...");

        foreach ($orders as $order) {
            $remaining = $order->total;
            $itemCount = 0;

            while ($remaining > 0 && $itemCount < 5) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $price = $product->price ?? 10.00;
                $subtotal = $quantity * $price;

                // If subtotal exceeds remaining, adjust quantity
                if ($subtotal > $remaining) {
                    $quantity = max(1, (int)floor($remaining / $price));
                    $subtotal = $quantity * $price;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal,
                ]);

                $remaining -= $subtotal;
                $itemCount++;
            }

            $this->line("✓ Order #{$order->id} - " . OrderItem::where('order_id', $order->id)->count() . " items added");
        }

        $this->info("✓ All orders populated successfully!");
    }
}
