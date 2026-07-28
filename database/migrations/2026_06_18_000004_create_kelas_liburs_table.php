<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_liburs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->restrictOnDelete();
            $table->date('tanggal');
            $table->string('keterangan', 255);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            // Satu kelas hanya boleh 1 libur per tanggal
            $table->unique(['kelas_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_liburs');
    }
};
