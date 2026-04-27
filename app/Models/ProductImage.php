<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProductImage extends Model
{
    use HasFactory;

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
            return "data:{$mime};base64," . base64_encode($this->image_data);
        }
        return 'https://via.placeholder.com/300x300/e0e0e0/666666?text=No+Image';
    }
}