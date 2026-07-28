<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini menyimpan SNAPSHOT data hafalan tahfidz per siswa per semester.
     * Data di-lock saat semester ditutup dan TIDAK PERNAH berubah.
     */
    public function up(): void
    {
        if (Schema::hasTable('rekap_tahfidz_semesters')) {
            return;
        }

        Schema::create('rekap_tahfidz_semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas')->restrictOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('guru_tartils')->nullOnDelete();
            $table->integer('total_juz_dihafal')->default(0);   // Jumlah juz unik status=hafal
            $table->integer('total_entry')->default(0);          // Total record hafalan
            $table->integer('juz_terakhir')->nullable();         // Juz terakhir dicatat
            $table->string('surat_terakhir', 100)->nullable();   // Nama surat terakhir
            $table->enum('kualitas_rata', ['mumtaz', 'jayyid_jiddan', 'jayyid', 'naqis'])->default('jayyid');
            $table->text('detail_juz')->nullable();              // JSON: [{juz, surat, status, kualitas, tanggal}]
            $table->timestamp('locked_at');                      // Waktu snapshot dibuat
            $table->timestamps();

            $table->unique(['semester_id', 'siswa_id'], 'unique_tahfidz_snap');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_tahfidz_semesters');
    }
};
