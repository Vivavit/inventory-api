<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProductImage extends Model
{
    use HasFactory;

    protected $hidden = [
        'image_data',
    ];

    protected $fillable = [
        'product_id', 'product_variant_id', 'image_path',
        'image_data', 'mime_type', 'alt_text', 'is_primary', 'sort_order',
    ];

    protected $casts = ['is_primary' => 'boolean', 'sort_order' => 'integer'];
    protected $appends = ['url'];

    public function product() { return $this->belongsTo(Product::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }

    public function getUrlAttribute()
    {
        if (!empty($this->image_data)) {
            $mime = $this->mime_type ?? 'image/jpeg';
            return "data:{$mime};base64," . $this->normalizeImageData($this->image_data);
        }
        return $this->getPlaceholderUrl();
    }

    private function getPlaceholderUrl(): string
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
}
