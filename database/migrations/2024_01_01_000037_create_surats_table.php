<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique();
            $table->string('nama_latin', 50);
            $table->smallInteger('jumlah_ayat')->unsigned();
            $table->string('jenis', 20); // Makkiyah / Madaniyah
            $table->smallInteger('urutan')->unsigned()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surats');
    }
};
