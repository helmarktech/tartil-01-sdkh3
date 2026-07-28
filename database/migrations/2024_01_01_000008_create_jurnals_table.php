<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnals', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('guru_id')->constrained('guru_tartils');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->string('surat', 100);
            $table->string('ayat', 50);
            $table->text('materi')->nullable();
            $table->enum('jenis_penilaian', ['harian', 'tengah_semester', 'akhir_semester'])->default('harian');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnals');
    }
};
