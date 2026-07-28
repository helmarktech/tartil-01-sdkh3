<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('munaqosyah_pendaftarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('munaqosyah_id')->constrained('ujian_munaqosyahs')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas');
            $table->enum('status', ['terdaftar', 'lulus', 'tidak_lulus', 'tidak_hadir'])->default('terdaftar');
            $table->integer('nilai')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('munaqosyah_pendaftarans');
    }
};
