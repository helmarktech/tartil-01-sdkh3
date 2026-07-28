<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan SNAPSHOT data munaqosyah per siswa per semester.
     * Data di-lock saat semester ditutup dan TIDAK PERNAH berubah.
     */
    public function up(): void
    {
        Schema::create('rekap_munaqosyah_semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->restrictOnDelete();
            $table->integer('total_ujian')->default(0);          // Jumlah ujian diikuti
            $table->integer('total_lulus')->default(0);           // Jumlah lulus
            $table->integer('total_tidak_lulus')->default(0);     // Jumlah tidak lulus
            $table->integer('total_terdaftar')->default(0);       // Masih terdaftar (belum dinilai)
            $table->decimal('rata_rata_nilai', 5, 2)->nullable(); // Rata-rata nilai
            $table->text('detail_ujian')->nullable();             // JSON: [{nama_ujian, tingkat, status, nilai, catatan}]
            $table->timestamp('locked_at');                       // Waktu snapshot dibuat
            $table->timestamps();

            // 1 siswa hanya 1 snapshot munaqosyah per semester
            $table->unique(['semester_id', 'siswa_id'], 'unique_munaqosyah_snap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_munaqosyah_semesters');
    }
};
