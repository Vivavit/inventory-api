<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id', 'brand_id', 'name', 'slug', 'sku',
        'short_description', 'description', 'price', 'compare_price',
        'cost_price', 'default_low_stock_threshold', 'manage_stock',
        'is_active', 'is_featured', 'has_variants', 'weight',
        'meta_title', 'meta_description', 'views_count', 'sold_count',
        'image_path', 'image_data', 'image_mime_type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'default_low_stock_threshold' => 'integer',
        'views_count' => 'integer',
        'sold_count' => 'integer',
        'manage_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'has_variants' => 'boolean',
    ];

    // Relationships
    public function category() { return $this->belongsTo(Category::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function variants() { return $this->hasMany(ProductVariant::class); }
    public function images() { return $this->hasMany(ProductImage::class)->orderBy('sort_order'); }
    public function primaryImage() { return $this->hasOne(ProductImage::class)->where('is_primary', true); }
    public function warehouseProducts() { return $this->hasMany(WarehouseProduct::class); }
    public function inventoryLocations() { return $this->hasMany(InventoryLocation::class); }
    public function orderItems() { return $this->hasMany(\App\Models\OrderItem::class); }

    // Accessors
    public function getTotalStockAttribute()
    {
        return $this->relationLoaded('warehouseProducts')
            ? $this->warehouseProducts->sum('quantity')
            : $this->warehouseProducts()->sum('quantity');
    }

    public function getStockStatusAttribute()
    {
        if ($this->total_stock <= 0) return 'out_of_stock';
        if ($this->total_stock <= $this->default_low_stock_threshold) return 'low_stock';
        return 'in_stock';
    }

    public function getImageUrlAttribute()
    {
        if (!empty($this->image_data)) {
            $mime = $this->image_mime_type ?? 'image/jpeg';
            return "data:{$mime};base64," . base64_encode($this->image_data);
        }
        return $this->getPlaceholderUrl();
    }

    private function getPlaceholderUrl()
    {
        return 'https://via.placeholder.com/300x300/e0e0e0/666666?text=No+Image';
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (empty($product->slug)) $product->slug = Str::slug($product->name);
            if (empty($product->sku)) $product->sku = 'SKU-'.strtoupper(Str::random(8));
        });
        static::created(function ($product) {
            $warehouses = Warehouse::where('is_active', true)->get();
            foreach ($warehouses as $warehouse) {
                WarehouseProduct::updateOrCreate(
                    ['warehouse_id' => $warehouse->id, 'product_id' => $product->id],
                    ['quantity' => 0]
                );
            }
        });
        static::updating(function ($product) {
            if ($product->isDirty('name')) $product->slug = Str::slug($product->name);
        });
    }
}