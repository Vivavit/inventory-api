<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'image_path',
        'alt_text',
        'is_primary',
        'sort_order',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    #[Scope]
    protected function primary(Builder $query): void
    {
        $query->where('is_primary', true);
    }

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    // ========== OPTIMIZED URL ACCESSOR ==========
    public function getUrlAttribute()
    {
        // 1. Check if image_path is empty or null
        if (empty($this->image_path)) {
            return $this->getPlaceholderUrl();
        }

        // 2. Clean the path (remove leading slashes if present)
        $cleanPath = ltrim($this->image_path, '/');

        // Always use asset() for consistent URLs
        return asset('storage/' . $cleanPath);
    }

    /**
     * Get placeholder image URL
     */
    private function getPlaceholderUrl()
    {
        // Try multiple placeholder options
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

        // Final fallback
        return 'https://via.placeholder.com/300x300/e0e0e0/666666?text=No+Image';
    }
}