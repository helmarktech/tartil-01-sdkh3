<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan SNAPSHOT data jurnal harian per siswa per semester.
     * Data di-lock saat semester ditutup dan TIDAK PERNAH berubah.
     * Digunakan untuk audit track record semester lalu.
     */
    public function up(): void
    {
        Schema::create('rekap_jurnal_semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->restrictOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('guru_tartils')->nullOnDelete();
            $table->integer('total_hari')->default(0);           // Total hari mengaji
            $table->integer('count_b')->default(0);               // Jumlah penilaian B
            $table->integer('count_c')->default(0);               // Jumlah penilaian C
            $table->integer('count_k')->default(0);               // Jumlah penilaian K
            $table->integer('r2_harian')->default(0);             // R2 Harian terkunci
            $table->integer('persentase_b')->default(0);          // Persentase B (count_b/total_hari * 100)
            $table->text('detail_surat')->nullable();             // JSON: [{surat, ayat_mulai, ayat_selesai, tanggal}]
            $table->text('detail_bulanan')->nullable();           // JSON: [{bulan, total, b, c, k, persentase}]
            $table->timestamp('locked_at');                       // Waktu snapshot dibuat
            $table->timestamps();

            // 1 siswa hanya 1 snapshot per semester per kelas
            $table->unique(['semester_id', 'kelas_id', 'siswa_id'], 'unique_jurnal_snap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_jurnal_semesters');
    }
};
