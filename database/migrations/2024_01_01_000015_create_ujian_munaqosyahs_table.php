<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ujian_munaqosyahs', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100); // Munaqosyah Unit 2024
            $table->enum('tingkat', ['unit', 'yayasan', 'pesantren']);
            $table->date('tanggal_ujian');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->enum('status', ['draft', 'pengajuan', 'disetujui', 'sedang_berlangsung', 'selesai', 'ditolak'])->default('draft');
            $table->foreignId('diajukan_oleh')->nullable()->constrained('guru_tartils')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ujian_munaqosyahs');
    }
};
