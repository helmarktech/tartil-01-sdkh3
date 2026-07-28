<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan SNAPSHOT riwayat perubahan kelas (perpindahan, kenaikan)
     * per siswa per semester. Data di-lock saat semester ditutup.
     */
    public function up(): void
    {
        Schema::create('rekap_riwayat_semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->restrictOnDelete();
            $table->foreignId('kelas_tartil_id')->nullable()->constrained('kelas')->nullOnDelete();     // Kelas tartil akhir semester
            $table->foreignId('kelas_reguler_id')->nullable()->constrained('kelas_regulers')->nullOnDelete(); // Kelas reguler akhir semester
            $table->integer('jumlah_pindah_tartil')->default(0);   // Berapa kali pindah tartil
            $table->integer('jumlah_pindah_reguler')->default(0);  // Berapa kali pindah reguler
            $table->integer('kenaikan_reguler')->default(0);       // 1 = naik kelas, 0 = tidak
            $table->text('detail_perpindahan')->nullable();        // JSON: [{jenis, dari_kelas, ke_kelas, tanggal, status}]
            $table->text('detail_kenaikan')->nullable();           // JSON: {dari_reguler, ke_reguler, tanggal, status}
            $table->timestamp('locked_at');                        // Waktu snapshot dibuat
            $table->timestamps();

            // 1 siswa hanya 1 snapshot riwayat per semester
            $table->unique(['semester_id', 'siswa_id'], 'unique_riwayat_snap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_riwayat_semesters');
    }
};
