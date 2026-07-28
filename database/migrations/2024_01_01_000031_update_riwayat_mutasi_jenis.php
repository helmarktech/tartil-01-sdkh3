<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }

        // MySQL tidak mendukung alter enum, pakai raw SQL
        DB::statement('ALTER TABLE riwayat_mutasis MODIFY jenis VARCHAR(30) NOT NULL');
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }

        DB::statement("ALTER TABLE riwayat_mutasis MODIFY jenis ENUM('nonaktifkan','aktifkan','hapus','pulihkan') NOT NULL");
    }
};
