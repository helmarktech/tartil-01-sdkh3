<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel ini mencatat progres hafalan Tahfidz per siswa.
     * Setiap row = 1 entry hafalan (juz, surat, ayat range, status, kualitas).
     */
    public function up(): void
    {
        if (Schema::hasTable('hafalan_tahfidzs')) {
            return;
        }

        Schema::create('hafalan_tahfidzs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->nullOnDelete();
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->nullOnDelete();
            $table->foreignId('surat_id')->nullable()->constrained('surats')->nullOnDelete();
            $table->tinyInteger('juz')->unsigned(); // 1 - 30
            $table->integer('ayat_mulai')->unsigned()->default(1);
            $table->integer('ayat_selesai')->unsigned()->nullable();
            $table->enum('status', ['baru', 'setengah_hafal', 'hafal', 'murajaah'])->default('baru');
            $table->enum('kualitas', ['mumtaz', 'jayyid_jiddan', 'jayyid', 'naqis'])->default('jayyid');
            $table->text('catatan')->nullable();
            $table->date('tanggal_hafalan');
            $table->foreignId('created_by')->nullable()->constrained('guru_tartils')->nullOnDelete();
            $table->timestamps();

            // Index untuk query cepat
            $table->index(['siswa_id', 'semester_id']);
            $table->index(['siswa_id', 'juz']);
            $table->index(['kelas_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hafalan_tahfidzs');
    }
};
