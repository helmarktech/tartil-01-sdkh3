<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cache R2 Akhir — dihitung sekali, dibaca berkali-kali
        // Ini menghindari kalkulasi ulang setiap request
        Schema::create('rekap_r2_akhirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->restrictOnDelete();
            $table->integer('r2_harian')->default(0);       // 0-100
            $table->integer('r2_penilaian')->default(0);    // 0-100
            $table->integer('r2_akhir')->default(0);        // (Harian + Penilaian) / 2
            $table->integer('jumlah_indikator')->default(0);
            $table->integer('jumlah_terisi')->default(0);
            $table->boolean('is_mutasi')->default(false);
            $table->timestamp('last_calculated')->useCurrent();
            $table->timestamps();

            // Unique: 1 rekap R2 per siswa per semester per kelas
            $table->unique(['semester_id', 'kelas_id', 'siswa_id'], 'idx_r2_unique');
            $table->index(['semester_id', 'kelas_id'], 'idx_r2_sem_kelas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_r2_akhirs');
    }
};
