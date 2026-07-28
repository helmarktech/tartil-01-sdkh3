<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_mutasis', function (Blueprint $table) {
            $table->id();
            $table->morphs('mutasi'); // siswa/guru polymorphic
            $table->string('jenis', 30); // nonaktifkan, aktifkan, hapus, pulihkan, mutasi_keluar, dll
            $table->text('keterangan');
            $table->foreignId('dilakukan_oleh')->constrained('users');
            $table->timestamp('tanggal_mutasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_mutasis');
    }
};
