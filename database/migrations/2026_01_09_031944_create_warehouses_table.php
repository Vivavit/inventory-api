<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();

            // Add contact information
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Add warehouse type/classification
            $table->enum('type', ['main', 'branch', 'store', 'virtual'])->default('main');

            // Add capacity/status
            $table->decimal('capacity', 10, 2)->nullable()->comment('Total capacity in square meters/units');
            $table->boolean('is_default')->default(false); // Default warehouse

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Optional: if you want to soft delete warehouses
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
