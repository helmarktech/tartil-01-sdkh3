<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah status 'pending' ke munaqosyah_pendaftarans
        $col = \DB::select("SHOW COLUMNS FROM munaqosyah_pendaftarans WHERE Field = 'status'");
        if (!empty($col) && str_contains($col[0]->Type, 'enum')) {
            \DB::statement("ALTER TABLE munaqosyah_pendaftarans MODIFY status ENUM('pending', 'terdaftar', 'lulus', 'tidak_lulus', 'tidak_hadir') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        $col = \DB::select("SHOW COLUMNS FROM munaqosyah_pendaftarans WHERE Field = 'status'");
        if (!empty($col) && str_contains($col[0]->Type, 'enum')) {
            \DB::statement("ALTER TABLE munaqosyah_pendaftarans MODIFY status ENUM('terdaftar', 'lulus', 'tidak_lulus', 'tidak_hadir') NOT NULL DEFAULT 'terdaftar'");
        }
    }
};
