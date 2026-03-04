<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->cascadeOnDelete();
            
            // Stock quantities
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->integer('available_quantity')->virtualAs('quantity - reserved_quantity');
            
            // Add location details within warehouse
            $table->string('aisle')->nullable();
            $table->string('rack')->nullable();
            $table->string('shelf')->nullable();
            $table->string('bin')->nullable();
            $table->string('location_code')->nullable()->comment('e.g., A-01-B-02');
            
            // Add reorder information
            $table->integer('reorder_point')->nullable();
            $table->integer('reorder_quantity')->nullable();
            
            // Add cost information (for FIFO/LIFO)
            $table->decimal('average_cost', 10, 2)->nullable();
            $table->decimal('last_purchase_cost', 10, 2)->nullable();
            
            $table->timestamps();
            
            // Unique constraint with SHORTER name
            $table->unique(['product_id', 'warehouse_id', 'product_variant_id'], 'inv_loc_product_warehouse_variant_unique');
            
            // Indexes for performance with shorter names
            $table->index(['warehouse_id', 'product_id'], 'inv_loc_warehouse_product');
            $table->index(['location_code'], 'inv_loc_location_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_locations');
    }
};