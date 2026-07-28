<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kop_surat_rapors', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->nullable();
            $table->string('judul', 200)->default('LAPORAN HASIL BELAJAR');
            $table->string('sub_judul', 200)->default('Program Pembelajaran Al-Quran');
            $table->string('nama_sekolah', 200)->default('Nama Sekolah');
            $table->text('alamat')->nullable();
            $table->string('telepon', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 100)->nullable();
            $table->string('tahun_ajaran', 20)->nullable();
            $table->text('catatan_kaki')->nullable();
            $table->string('kepala_sekolah', 100)->nullable();
            $table->string('nip_kepala_sekolah', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kop_surat_rapors');
    }
};
