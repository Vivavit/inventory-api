<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL/PostgreSQL, we can modify the column
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft', 'pending', 'ordered', 'partially_received', 'received', 'completed', 'cancelled') NOT NULL DEFAULT 'draft'");
        } else {
            // For SQLite and other databases, we'll just update existing records
            // The enum constraint is handled at application level
            // No actual schema change needed for SQLite
        }
    }

    public function down(): void
    {
        // For MySQL/PostgreSQL, revert the enum
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('draft', 'pending', 'ordered', 'partially_received', 'received', 'cancelled') NOT NULL DEFAULT 'draft'");
        }
        // For SQLite, no action needed
    }
};
