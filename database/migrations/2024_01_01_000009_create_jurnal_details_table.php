<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurnal_id')->constrained('jurnals')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswas');
            // Penilaian B, C, K
            $table->integer('nilai_b')->default(0); // Bacaan (0-100)
            $table->integer('nilai_c')->default(0); // Catatan/Pengetahuan (0-100)
            $table->integer('nilai_k')->default(0); // Keterampilan/Sikap (0-100)
            $table->integer('nilai_akhir')->virtualAs('(nilai_b + nilai_c + nilai_k) / 3');
            $table->enum('predikat', ['A', 'B', 'C', 'D', 'E'])->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_details');
    }
};
