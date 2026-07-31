<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('jenis', 20); // tartil / reguler
            $table->string('file_name')->nullable();
            $table->string('status', 20)->default('pending'); // pending, processing, success, failed
            $table->unsignedInteger('sukses')->default(0);
            $table->unsignedInteger('gagal')->default(0);
            $table->longText('errors')->nullable(); // JSON array
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['jenis', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_logs');
    }
};
