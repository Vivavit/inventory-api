<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            if (!Schema::hasColumn('product_images', 'image_data')) {
                $table->longText('image_data')->nullable();
            }
            if (!Schema::hasColumn('product_images', 'mime_type')) {
                $table->string('mime_type')->default('image/jpeg')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'mime_type']);
        });
    }
};