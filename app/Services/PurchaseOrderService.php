<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseProduct;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurchaseOrderService
{
    public function query(?array $statuses = null): Builder
    {
        return PurchaseOrder::with(['supplier', 'creator', 'items.product'])
            ->when($statuses, fn (Builder $query) => $query->whereIn('status', $statuses));
    }

    public function applyFilters(Builder $query, ?string $search = null, ?string $status = null): Builder
    {
        return $query
            ->when($search, function (Builder $query, string $search) {
                $query->where(function (Builder $subQuery) use ($search) {
                    $subQuery->where('po_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn (Builder $supplierQuery) => $supplierQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status, fn (Builder $query, string $status) => $query->where('status', $status));
    }

    public function getFormDependencies(): array
    {
        return [
            'suppliers' => Supplier::where('is_active', true)->get(),
            'products' => Product::where('is_active', true)->get(),
            'warehouses' => Warehouse::where('is_active', true)->get(),
        ];
    }

    public function create(array $validated): PurchaseOrder
    {
        return DB::transaction(function () use ($validated) {
            $totals = $this->calculateTotals($validated);

            $purchaseOrder = PurchaseOrder::create($this->buildAttributes(
                validated: $validated,
                totals: $totals,
                extra: [
                    'po_number' => PurchaseOrder::generatePONumber(),
                    'status' => $validated['status'] ?? 'pending',
                    'created_by' => Auth::id(),
                ],
            ));

            $this->syncItems($purchaseOrder, $validated['items']);

            return $purchaseOrder;
        });
    }

    public function update(PurchaseOrder $purchaseOrder, array $validated): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $validated) {
            $totals = $this->calculateTotals($validated);

            $purchaseOrder->update($this->buildAttributes(
                validated: $validated,
                totals: $totals,
            ));

            $purchaseOrder->items()->delete();
            $this->syncItems($purchaseOrder, $validated['items']);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'warehouse', 'creator']);
        });
    }

    public function confirm(PurchaseOrder $purchaseOrder, bool $withFinancialTransactionFields = false): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $withFinancialTransactionFields) {
            $purchaseOrder->loadMissing('items');
            $purchaseOrder->update(['status' => 'completed']);

            foreach ($purchaseOrder->items as $item) {
                $warehouseProduct = WarehouseProduct::firstOrCreate(
                    [
                        'warehouse_id' => $purchaseOrder->warehouse_id,
                        'product_id' => $item->product_id,
                    ],
                    ['quantity' => 0]
                );

                $quantityBefore = $warehouseProduct->quantity;
                $warehouseProduct->increment('quantity', $item->quantity);
                $quantityAfter = $warehouseProduct->fresh()->quantity;

                $transactionData = [
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                    'type' => 'purchase',
                    'user_id' => Auth::id(),
                    'reference_type' => 'purchase_order',
                    'reference_id' => $purchaseOrder->id,
                    'quantity_change' => $item->quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'notes' => "Stock increase from PO {$purchaseOrder->po_number}",
                ];

                if ($withFinancialTransactionFields) {
                    $transactionData['price'] = $item->unit_price;
                    $transactionData['total'] = $item->quantity * $item->unit_price;
                }

                InventoryTransaction::create($transactionData);
                $item->update(['received_quantity' => $item->quantity]);
            }

            return $purchaseOrder->fresh(['items.product', 'supplier', 'warehouse', 'creator']);
        });
    }

    public function updateStatus(PurchaseOrder $purchaseOrder, string $status): PurchaseOrder
    {
        $purchaseOrder->update(['status' => $status]);

        return $purchaseOrder->refresh();
    }

    public function receive(PurchaseOrder $purchaseOrder, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $items) {
            $purchaseOrder->loadMissing('items');

            foreach ($items as $itemData) {
                $item = $purchaseOrder->items->firstWhere('id', (int) $itemData['id']);

                if (! $item) {
                    throw new \RuntimeException('Invalid purchase item selected.');
                }

                $received = (int) $itemData['received'];
                $remaining = $item->quantity - $item->received_quantity;

                if ($received < 0 || $received > $remaining) {
                    $itemName = optional($item->product)->name ?: 'item';
                    throw new \RuntimeException("Received quantity for {$itemName} exceeds the remaining amount.");
                }

                if ($received === 0) {
                    continue;
                }

                $warehouseProduct = WarehouseProduct::firstOrCreate(
                    [
                        'warehouse_id' => $purchaseOrder->warehouse_id,
                        'product_id' => $item->product_id,
                    ],
                    ['quantity' => 0]
                );

                $quantityBefore = $warehouseProduct->quantity;
                $warehouseProduct->increment('quantity', $received);
                $quantityAfter = $warehouseProduct->fresh()->quantity;

                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                    'type' => 'purchase',
                    'price' => $item->unit_price,
                    'total' => $received * $item->unit_price,
                    'user_id' => Auth::id(),
                    'reference_type' => 'purchase_order',
                    'reference_id' => $purchaseOrder->id,
                    'quantity_change' => $received,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'notes' => "Stock received from PO {$purchaseOrder->po_number}",
                ]);

                $item->increment('received_quantity', $received);
            }

            $purchaseOrder->refresh()->load('items');
            $allReceived = $purchaseOrder->items->every(fn (PurchaseOrderItem $item) => $item->received_quantity >= $item->quantity);

            $purchaseOrder->update([
                'status' => $allReceived ? 'received' : 'partially_received',
                'actual_delivery_date' => $allReceived ? now()->toDateString() : null,
            ]);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'warehouse', 'creator']);
        });
    }

    public function getProductHistory(Product $product): array
    {
        $history = InventoryTransaction::with('warehouse', 'user')
            ->where('product_id', $product->id)
            ->latest()
            ->limit(25)
            ->get()
            ->map(fn ($record) => [
                'date' => $record->created_at->format('M d, Y H:i'),
                'warehouse' => $record->warehouse?->name,
                'type' => ucfirst($record->type),
                'change' => $record->quantity_change,
                'before' => $record->quantity_before,
                'after' => $record->quantity_after,
                'notes' => $record->notes,
                'user' => $record->user?->name,
            ]);

        return [
            'product' => ['id' => $product->id, 'name' => $product->name],
            'history' => $history,
        ];
    }

    public function toDetailPayload(PurchaseOrder $purchaseOrder): array
    {
        $purchaseOrder->loadMissing(['supplier', 'creator', 'items.product', 'warehouse']);

        $data = $purchaseOrder->toArray();
        $data['order_date_formatted'] = $purchaseOrder->order_date?->format('M d, Y');
        $data['expected_delivery_date_formatted'] = $purchaseOrder->expected_delivery_date?->format('M d, Y');

        return $data;
    }

    protected function calculateTotals(array $validated): array
    {
        $subtotal = collect($validated['items'] ?? [])
            ->sum(fn (array $item) => $item['quantity'] * $item['unit_price']);

        $shippingCost = (float) ($validated['shipping_cost'] ?? 0);

        return [
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'total_amount' => $subtotal + $shippingCost,
        ];
    }

    protected function buildAttributes(array $validated, array $totals, array $extra = []): array
    {
        $attributes = [
            'supplier_id' => $validated['supplier_id'],
            'warehouse_id' => $validated['warehouse_id'] ?? null,
            'order_date' => $validated['order_date'],
            'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
            'subtotal' => $totals['subtotal'],
            'shipping_cost' => $totals['shipping_cost'],
            'total_amount' => $totals['total_amount'],
            'notes' => $validated['notes'] ?? null,
        ];

        if (Schema::hasColumn('purchase_orders', 'payment_terms') && array_key_exists('payment_terms', $validated)) {
            $attributes['payment_terms'] = $validated['payment_terms'] ?: null;
        }

        return array_merge($attributes, $extra);
    }

    protected function syncItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        $payload = collect($items)
            ->map(fn (array $item) => [
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['quantity'] * $item['unit_price'],
                'received_quantity' => 0,
            ])
            ->all();

        PurchaseOrderItem::insert($payload);
    }
}
