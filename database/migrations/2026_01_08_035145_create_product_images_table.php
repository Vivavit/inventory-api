<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If table doesn't exist, create it
        if (!Schema::hasTable('product_images')) {
            Schema::create('product_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
                $table->longText('image_data')->nullable();
                $table->string('mime_type')->default('image/jpeg')->nullable();
                $table->string('image_path')->nullable();
                $table->string('alt_text')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        } else {
            // If table exists, just add columns if they don't exist
            Schema::table('product_images', function (Blueprint $table) {
                if (!Schema::hasColumn('product_images', 'image_data')) {
                    $table->longText('image_data')->nullable();
                }
                if (!Schema::hasColumn('product_images', 'mime_type')) {
                    $table->string('mime_type')->default('image/jpeg')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};