<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('penilaian_rapors');
        Schema::dropIfExists('semester_penilaian_rapors');
    }

    public function down(): void
    {
        // Tidak bisa rollback — tabel lama sudah dihapus permanen
    }
};
