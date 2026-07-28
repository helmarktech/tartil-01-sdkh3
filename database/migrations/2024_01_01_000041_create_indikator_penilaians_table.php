<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indikator_penilaians', function (Blueprint $table) {
            $table->id();
            $table->string('jenis_kelas', 20)->comment('BQ1, BQ2, BQ3, BQ4, dll');
            $table->string('nama_indikator', 100);
            $table->unsignedTinyInteger('urutan')->default(0);
            $table->boolean('is_default')->default(false)->comment('True jika dari seeder, tidak bisa dihapus');
            $table->timestamps();

            $table->index(['jenis_kelas', 'urutan'], 'idx_indikator_jenis_urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indikator_penilaians');
    }
};
