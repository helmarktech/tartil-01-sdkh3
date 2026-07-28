<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekap_jurnal_bulanans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->integer('bulan')->unsigned()->comment('Format YYYYMM, contoh: 202601');
            $table->smallInteger('total_hadir')->unsigned()->default(0);
            $table->smallInteger('total_izin')->unsigned()->default(0);
            $table->smallInteger('total_sakit')->unsigned()->default(0);
            $table->smallInteger('total_alpa')->unsigned()->default(0);
            $table->smallInteger('count_b')->unsigned()->default(0)->comment('Baik');
            $table->smallInteger('count_c')->unsigned()->default(0)->comment('Cukup');
            $table->smallInteger('count_k')->unsigned()->default(0)->comment('Kurang');
            $table->decimal('rata_rata', 3, 2)->nullable()->comment('1.00=Baik, 0.67=Cukup, 0.33=Kurang');
            $table->timestamps();

            // Unique untuk agregasi UPSERT
            $table->unique(['semester_id', 'kelas_id', 'siswa_id', 'bulan'], 'idx_rekap_unique');
            $table->index(['kelas_id', 'bulan'], 'idx_rekap_kelas_bulan');
            $table->index(['siswa_id', 'bulan'], 'idx_rekap_siswa_bulan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekap_jurnal_bulanans');
    }
};
