<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kenaikan_kelas_regulers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas');
            $table->foreignId('kelas_reguler_lama_id')->constrained('kelas_regulers');
            $table->foreignId('kelas_reguler_baru_id')->constrained('kelas_regulers');
            $table->foreignId('semester_id')->constrained('semesters');
            $table->string('tahun_ajaran', 20);
            $table->foreignId('approved_by')->constrained('users');
            $table->timestamp('approved_at');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kenaikan_kelas_regulers');
    }
};
