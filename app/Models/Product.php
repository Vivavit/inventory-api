<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'compare_price',
        'cost_price',
        'default_low_stock_threshold',
        'manage_stock',
        'is_active',
        'is_featured',
        'has_variants',
        'weight',
        'meta_title',
        'meta_description',
        'views_count',
        'sold_count',
        'image_path', // Add image_path for direct image reference
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

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function warehouseProducts()
    {
        return $this->hasMany(WarehouseProduct::class);
    }

    public function inventoryLocations()
    {
        return $this->hasMany(InventoryLocation::class);
    }

    public function getTotalStockAttribute()
    {
        // Use eager loaded relation if available to avoid N+1 queries
        if ($this->relationLoaded('warehouseProducts')) {
            return $this->warehouseProducts->sum('quantity');
        }

        return $this->warehouseProducts()->sum('quantity');
    }

    public function getStockStatusAttribute()
    {
        if ($this->total_stock <= 0) {
            return 'out_of_stock';
        }

        if ($this->total_stock <= $this->default_low_stock_threshold) {
            return 'low_stock';
        }

        return 'in_stock';
    }

    public function getImageUrlAttribute()
    {
        if (empty($this->image_path)) {
            return $this->getPlaceholderUrl();
        }

        $cleanPath = ltrim($this->image_path, '/');

        // Always use asset() for consistent URLs
        return asset('storage/' . $cleanPath);
    }

    protected static function boot()
    {
        parent::boot();

        // Before create
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }

            if (empty($product->sku)) {
                $product->sku = 'SKU-'.strtoupper(Str::random(8));
            }
        });

        // After create → create stock rows per warehouse
        static::created(function ($product) {
            $warehouses = Warehouse::where('is_active', true)->get();

            foreach ($warehouses as $warehouse) {
                WarehouseProduct::updateOrCreate(
                    [
                        'warehouse_id' => $warehouse->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'quantity' => 0,
                    ]
                );
            }
        });

        // Before update
        static::updating(function ($product) {
            if ($product->isDirty('name')) {
                $product->slug = Str::slug($product->name);
            }
        });
    }
    public function orderItems()
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }

    // ========== IMAGE URL ACCESSORS ==========

    /**
     * Get primary image from ProductImage relationship
     */
    public function getPrimaryImageAttribute()
    {
        return $this->images()->where('is_primary', true)->first() ?? $this->images()->first();
    }

    /**
     * Get all image URLs as array
     */
    public function getAllImageUrlsAttribute()
    {
        $urls = [];
        
        // Add primary image from image_path if exists
        if (!empty($this->image_path)) {
            $urls[] = $this->image_url;
        }
        
        // Add gallery images
        foreach ($this->images as $image) {
            $urls[] = $image->url;
        }
        
        return array_unique($urls);
    }

    /**
     * Get placeholder image URL
     */
    private function getPlaceholderUrl()
    {
        $placeholders = [
            'images/product-default.svg',
            'images/product-placeholder.jpg',
            'images/no-image.png',
            'https://via.placeholder.com/300x300/e0e0e0/666666?text=No+Image'
        ];

        foreach ($placeholders as $placeholder) {
            if (str_starts_with($placeholder, 'http')) {
                return $placeholder;
            }
            
            $fullPath = public_path($placeholder);
            if (file_exists($fullPath)) {
                return asset($placeholder);
            }
        }

        return 'https://via.placeholder.com/300x300/e0e0e0/666666?text=No+Image';
    }
    
}
