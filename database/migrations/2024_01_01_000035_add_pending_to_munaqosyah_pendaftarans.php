<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }

        // Tambah status 'pending' ke munaqosyah_pendaftarans
        $col = DB::select("SHOW COLUMNS FROM munaqosyah_pendaftarans WHERE Field = 'status'");
        if (! empty($col) && str_contains($col[0]->Type, 'enum')) {
            DB::statement("ALTER TABLE munaqosyah_pendaftarans MODIFY status ENUM('pending', 'terdaftar', 'lulus', 'tidak_lulus', 'tidak_hadir') NOT NULL DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }

        $col = DB::select("SHOW COLUMNS FROM munaqosyah_pendaftarans WHERE Field = 'status'");
        if (! empty($col) && str_contains($col[0]->Type, 'enum')) {
            DB::statement("ALTER TABLE munaqosyah_pendaftarans MODIFY status ENUM('terdaftar', 'lulus', 'tidak_lulus', 'tidak_hadir') NOT NULL DEFAULT 'terdaftar'");
        }
    }
};
