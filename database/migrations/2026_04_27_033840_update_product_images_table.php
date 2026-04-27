<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'image_data')) {
                $table->longText('image_data')->nullable();
            }
            if (!Schema::hasColumn('products', 'image_mime_type')) {
                $table->string('image_mime_type')->default('image/jpeg')->nullable();
            }
            // Keep image_path for legacy support
            if (!Schema::hasColumn('products', 'image_path')) {
                $table->string('image_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_data', 'image_mime_type', 'image_path']);
        });
    }
};