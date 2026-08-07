<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel untuk mencatat laporan pendampingan orang tua siswa
     * (tadarus dan murajaah) yang ditujukan ke guru kelas tartil.
     */
    public function up(): void
    {
        Schema::create('laporan_pendampingan_ortus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->foreignId('guru_id')->nullable()->constrained('guru_tartils')->nullOnDelete()->comment('Guru kelas tartil tujuan laporan');
            $table->enum('jenis', ['tadarus', 'murajaah'])->default('tadarus');
            $table->foreignId('surat_id')->nullable()->constrained('surats')->nullOnDelete();
            $table->integer('ayat_mulai')->unsigned()->nullable();
            $table->integer('ayat_selesai')->unsigned()->nullable();
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->enum('status', ['pengajuan_konfirmasi', 'telah_dikonfirmasi'])->default('pengajuan_konfirmasi');
            $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('guru_tartils')->nullOnDelete()->comment('Guru yang mengkonfirmasi');
            $table->dateTime('tanggal_konfirmasi')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
            $table->index(['kelas_id', 'status']);
            $table->index('guru_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_pendampingan_ortus');
    }
};
