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

    protected $hidden = [
        'image_data',
    ];

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
            return "data:{$mime};base64," . $this->normalizeImageData($this->image_data);
        }
        return $this->getPlaceholderUrl();
    }

    private function getPlaceholderUrl()
    {
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">
  <rect width="300" height="300" fill="#e0e0e0"/>
  <path d="M90 200l45-50 35 30 30-40 50 60H90z" fill="#9a9a9a"/>
  <circle cx="120" cy="110" r="18" fill="#9a9a9a"/>
  <text x="150" y="255" text-anchor="middle" font-family="Arial, sans-serif" font-size="24" fill="#666666">No Image</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,'.rawurlencode($svg);
    }

    private function normalizeImageData(string $value): string
    {
        $decoded = base64_decode($value, true);

        if ($decoded !== false && $this->looksLikeImageBinary($decoded)) {
            return $value;
        }

        return base64_encode($value);
    }

    private function looksLikeImageBinary(string $value): bool
    {
        return str_starts_with($value, "\xFF\xD8\xFF")
            || str_starts_with($value, "\x89PNG\r\n\x1A\n")
            || str_starts_with($value, 'GIF87a')
            || str_starts_with($value, 'GIF89a')
            || str_starts_with($value, 'RIFF')
            || str_starts_with($value, 'BM');
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
