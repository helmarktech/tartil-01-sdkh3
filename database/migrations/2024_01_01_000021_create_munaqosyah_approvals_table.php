<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('munaqosyah_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_id')->constrained('munaqosyah_pendaftarans')->onDelete('cascade');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('munaqosyah_approvals');
    }
};
