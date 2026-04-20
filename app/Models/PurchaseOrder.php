<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'status',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Receive stock for this purchase order
     * Updates warehouse product quantities and marks PO as received
     */
    public function receiveStock()
    {
        if (! $this->warehouse_id) {
            throw new \Exception('Cannot receive stock: No warehouse assigned to this purchase order');
        }

        foreach ($this->items as $item) {
            // Update warehouse product stock
            $warehouseProduct = WarehouseProduct::firstOrCreate(
                [
                    'warehouse_id' => $this->warehouse_id,
                    'product_id' => $item->product_id,
                ],
                ['quantity' => 0]
            );

            $warehouseProduct->increment('quantity', $item->quantity - $item->received_quantity);

            // Update received quantity
            $item->update(['received_quantity' => $item->quantity]);
        }

        // Update PO status
        $this->update([
            'status' => 'received',
            'actual_delivery_date' => now()->toDateString(),
        ]);

        return $this;
    }

    /**
     * Generate unique PO number
     */
    public static function generatePONumber()
    {
        $lastPO = static::latest()->first();
        $lastNumber = $lastPO ? (int) substr($lastPO->po_number, -6) : 0;

        return 'PO-'.str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }
}
