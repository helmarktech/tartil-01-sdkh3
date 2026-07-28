<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Audit trail — siapa mengubah apa dan kapan
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('loggable_type', 100);  // Siswa, Kelas, JurnalHarian, dll
            $table->unsignedBigInteger('loggable_id');
            $table->string('action', 50);          // create, update, delete, login, logout
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_type', 20)->nullable(); // admin, guru, siswa
            $table->timestamps();

            $table->index(['loggable_type', 'loggable_id'], 'idx_loggable');
            $table->index(['action', 'created_at'], 'idx_action_date');
            $table->index('user_id', 'idx_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
