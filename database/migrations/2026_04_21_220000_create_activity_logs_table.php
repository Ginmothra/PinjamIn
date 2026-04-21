<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('log_type'); // misal: USERS, ITEMS, LOANS
            $table->string('action');   // INSERT, UPDATE, DELETE
            $table->string('table_name');
            $table->unsignedBigInteger('record_id');
            $table->text('old_data')->nullable();
            $table->text('new_data')->nullable();
            $table->string('performed_by')->nullable(); // Username atau ID (jika terdeteksi)
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
