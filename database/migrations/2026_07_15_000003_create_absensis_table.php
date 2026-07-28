<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('jurnal_id')->constrained('jurnals')->onDelete('cascade');
            $table->enum('status', ['Hadir', 'Sakit', 'Izin', 'Alpha'])->default('Hadir');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['siswa_id', 'status']);
            $table->index(['jurnal_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
