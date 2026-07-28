<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop kalau ada sisa
        Schema::dropIfExists('penilaian_rapor_nilais');
        Schema::dropIfExists('penilaian_rapor_internal_pesertas');
        Schema::dropIfExists('penilaian_rapor_internals');
        Schema::dropIfExists('penilaian_rapor_pendaftarans');
        Schema::dropIfExists('ujian_penilaian_rapors');
        Schema::dropIfExists('penilaian_rapor_toggles');

        // 1. Master penilaian rapor internal (admin buat)
        Schema::create('penilaian_rapor_internals', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->foreignId('semester_id')->unique()->constrained('semesters')->restrictOnDelete();
            $table->enum('status', ['aktif', 'selesai'])->default('aktif');
            $table->timestamps();
        });

        // 2. Nilai per siswa per indikator (guru isi)
        // Jumlah row = siswa × indikator sesuai jenis kelas
        Schema::create('penilaian_rapor_nilais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_id')->constrained('penilaian_rapor_internals')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas');
            $table->foreignId('indikator_penilaian_id')->constrained('indikator_penilaians');
            $table->tinyInteger('nilai')->unsigned()->nullable(); // 1-100
            $table->text('catatan')->nullable();
            $table->foreignId('diisi_oleh')->nullable()->constrained('guru_tartils');
            $table->timestamp('tanggal_diisi')->nullable();
            $table->timestamps();

            // 1 siswa hanya 1 nilai per indikator per penilaian
            $table->unique(['penilaian_id', 'siswa_id', 'indikator_penilaian_id'], 'unique_nilai_indikator');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_rapor_nilais');
        Schema::dropIfExists('penilaian_rapor_internals');
    }
};
