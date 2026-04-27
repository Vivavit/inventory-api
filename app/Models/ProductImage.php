<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_variant_id',
        'image_path',
        'image_data',
        'mime_type',
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

    // ========== IMAGE URL ACCESSOR ==========
    public function getUrlAttribute()
    {
        // Priority 1: If image_data exists (stored in database), convert to data URL
        if (!empty($this->image_data)) {
            return $this->getImageDataUrl();
        }

        // Priority 2: If image_path exists (legacy file storage), use file URL
        if (!empty($this->image_path)) {
            return $this->getFileUrl();
        }

        // Priority 3: Return placeholder
        return $this->getPlaceholderUrl();
    }

    /**
     * Convert binary image data to data URL for display
     */
    private function getImageDataUrl()
    {
        try {
            $mimeType = $this->mime_type ?? 'image/jpeg';
            
            // If image_data is already a string (base64 or raw)
            if (is_string($this->image_data)) {
                // Check if it's already base64
                if (preg_match('/^[A-Za-z0-9+\/=]+$/', $this->image_data)) {
                    return "data:{$mimeType};base64," . $this->image_data;
                }
                // If it's raw binary, encode it
                return "data:{$mimeType};base64," . base64_encode($this->image_data);
            }

            return $this->getPlaceholderUrl();
        } catch (\Exception $e) {
            Log::error('Error generating image data URL: ' . $e->getMessage());
            return $this->getPlaceholderUrl();
        }
    }

    /**
     * Get file URL from storage
     */
    private function getFileUrl()
    {
        $cleanPath = ltrim($this->image_path, '/');
        return asset('storage/' . $cleanPath);
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