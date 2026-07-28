<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas_regulers', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50); // 7A, 8B, 9C
            $table->integer('jenjang'); // 1 - 6
            $table->string('tingkat', 20); // rombel bebas: A, B, Putra, Putri, dll
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas_regulers');
    }
};
