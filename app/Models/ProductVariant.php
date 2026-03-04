<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'options',
        'price',
        'compare_price',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    // INVENTORY RELATIONSHIPS (NEW)
    public function inventoryLocations()
    {
        return $this->hasMany(InventoryLocation::class);
    }

    public function inventoryTransactions()
    {
        return $this->hasMany(InventoryTransaction::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function($variant) {
            if (empty($variant->sku)) {
                $variant->sku = 'VAR-'. strtoupper(Str::random(8));
            }
        });
    }
}