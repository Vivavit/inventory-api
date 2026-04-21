<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\WarehouseProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Initialize or retrieve cart for the authenticated user.
     */
    public function init(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $cart = Cart::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'warehouse_id' => $request->warehouse_id,
            ],
            ['total' => 0]
        );

        return response()->json([
            'success' => true,
            'cart' => $cart->load('items.product'),
        ]);
    }

    /**
     * Get current cart.
     */
    public function show(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $cart = Cart::where('user_id', auth()->id())
            ->where('warehouse_id', $request->warehouse_id)
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'cart' => $cart->load('items.product'),
        ]);
    }

    /**
     * Add item to cart.
     */
    public function addItem(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            // Get or create cart
            $cart = Cart::firstOrCreate(
                [
                    'user_id' => auth()->id(),
                    'warehouse_id' => $request->warehouse_id,
                ],
                ['total' => 0]
            );

            // Check product exists and get price
            $product = Product::findOrFail($request->product_id);

            // Check stock availability
            $stock = WarehouseProduct::where('warehouse_id', $request->warehouse_id)
                ->where('product_id', $request->product_id)
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->quantity < $request->quantity) {
                abort(400, 'Not enough stock available');
            }

            // Check if item already in cart
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $request->product_id)
                ->where('product_variant_id', $request->product_variant_id)
                ->first();

            if ($cartItem) {
                $cartItem->increment('quantity', $request->quantity);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $request->product_id,
                    'product_variant_id' => $request->product_variant_id,
                    'quantity' => $request->quantity,
                    'price' => $product->price,
                    'discount_amount' => 0,
                ]);
            }

            $this->recalculateCartTotal($cart);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'cart' => $cart->load('items.product'),
            ]);
        });
    }

    /**
     * Update cart item quantity.
     */
    public function updateItem(Request $request, CartItem $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request, $cartItem) {
            $cart = $cartItem->cart;

            // Check stock
            $stock = WarehouseProduct::where('warehouse_id', $cart->warehouse_id)
                ->where('product_id', $cartItem->product_id)
                ->lockForUpdate()
                ->first();

            if (!$stock || $stock->quantity < $request->quantity) {
                abort(400, 'Not enough stock for requested quantity');
            }

            $cartItem->update(['quantity' => $request->quantity]);
            $this->recalculateCartTotal($cart);

            return response()->json([
                'success' => true,
                'message' => 'Item updated',
                'cart' => $cart->load('items.product'),
            ]);
        });
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(CartItem $cartItem)
    {
        $cart = $cartItem->cart;
        $cartItem->delete();
        $this->recalculateCartTotal($cart);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart' => $cart->load('items.product'),
        ]);
    }

    /**
     * Clear all items from cart.
     */
    public function clear(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        $cart = Cart::where('user_id', auth()->id())
            ->where('warehouse_id', $request->warehouse_id)
            ->first();

        if ($cart) {
            $cart->items()->delete();
            $cart->update(['total' => 0]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }

    /**
     * Recalculate and update cart total.
     */
    private function recalculateCartTotal(Cart $cart)
    {
        $total = $cart->items()->sum(
            DB::raw('(quantity * price) - discount_amount')
        );

        $cart->update(['total' => $total ?? 0]);
    }
}

