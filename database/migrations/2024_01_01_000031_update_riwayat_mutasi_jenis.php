<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL tidak mendukung alter enum, pakai raw SQL
        \DB::statement("ALTER TABLE riwayat_mutasis MODIFY jenis VARCHAR(30) NOT NULL");
    }

    public function down(): void
    {
        \DB::statement("ALTER TABLE riwayat_mutasis MODIFY jenis ENUM('nonaktifkan','aktifkan','hapus','pulihkan') NOT NULL");
    }
};
