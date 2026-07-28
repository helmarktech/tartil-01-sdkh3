<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semester_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->integer('jumlah_siswa')->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['semester_id', 'kelas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_kelas');
    }
};
