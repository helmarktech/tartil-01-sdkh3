<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('guru_tartils')->onDelete('cascade');
            $table->date('tanggal');
            $table->tinyInteger('pertemuan_ke')->unsigned()->nullable()->comment('Pertemuan ke-');
            $table->string('halaman_juz', 50)->nullable()->comment('Contoh: Juz 1 hal 23-25');
            $table->foreignId('surat_id')->nullable()->constrained('surats')->onDelete('set null');
            $table->string('ayat', 50)->nullable()->comment('Contoh: 1-5');
            $table->string('materi_pembelajaran', 255)->nullable();
            $table->string('topik', 255)->nullable();
            $table->string('rencana', 255)->nullable()->comment('Rencana pertemuan berikutnya');
            $table->text('catatan_kelas')->nullable()->comment('Catatan umum untuk kelas');
            $table->timestamps();

            // Unique: hanya 1 jurnal per kelas per tanggal
            $table->unique(['kelas_id', 'tanggal'], 'idx_jurnal_kelas_unique');
            $table->index(['semester_id', 'tanggal'], 'idx_jurnal_kelas_semester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_kelas');
    }
};
