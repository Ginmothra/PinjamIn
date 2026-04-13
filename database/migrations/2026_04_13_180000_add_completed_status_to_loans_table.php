<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('pending', 'approved', 'borrowed', 'returned', 'completed', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE loans SET status = 'returned' WHERE status = 'completed'");
            DB::statement("ALTER TABLE loans MODIFY COLUMN status ENUM('pending', 'approved', 'borrowed', 'returned', 'rejected', 'cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
