<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            return;
        }

        // Robust: check current column type first
        $col = DB::select("SHOW COLUMNS FROM riwayat_mutasis WHERE Field = 'jenis'");
        if (! empty($col) && str_contains($col[0]->Type, 'enum')) {
            DB::statement('ALTER TABLE riwayat_mutasis MODIFY jenis VARCHAR(30) NOT NULL');
        }
    }

    public function down(): void
    {
        // no-op
    }
};
