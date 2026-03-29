<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_warehouse_id',
        'to_warehouse_id',
        'status',
        'request_date',
        'expected_transfer_date',
        'actual_transfer_date',
        'notes',
        'requested_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'request_date' => 'date',
        'expected_transfer_date' => 'date',
        'actual_transfer_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function fromWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse()
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items()
    {
        return $this->hasMany(StockTransferItem::class);
    }

    // Helper methods
    public function getTotalItemsAttribute()
    {
        return $this->items()->sum('quantity_requested');
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->status === 'received') {
            return 100;
        }

        $received = $this->items()->sum('quantity_received');
        $requested = $this->items()->sum('quantity_requested');

        return $requested > 0 ? ($received / $requested) * 100 : 0;
    }
}
