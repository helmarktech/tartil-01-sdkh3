<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas')->onDelete('set null');
            $table->enum('status_siswa', ['aktif', 'pindah', 'keluar', 'nonaktif'])->default('aktif');
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['semester_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_siswa');
    }
};
