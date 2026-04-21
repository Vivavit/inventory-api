<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('till_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('till_id')->constrained('tills')->cascadeOnDelete();
            $table->enum('type', ['sale', 'refund', 'deposit', 'withdrawal'])->default('sale');
            $table->decimal('amount', 12, 2);
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['till_id', 'type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('till_transactions');
    }
};
