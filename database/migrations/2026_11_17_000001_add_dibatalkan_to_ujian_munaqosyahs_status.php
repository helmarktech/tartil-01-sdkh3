<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STATUS_LENGKAP = ['draft', 'pengajuan', 'disetujui', 'sedang_berlangsung', 'selesai', 'ditolak', 'dibatalkan'];

    private const STATUS_LAMA = ['draft', 'pengajuan', 'disetujui', 'sedang_berlangsung', 'selesai', 'ditolak'];

    public function up(): void
    {
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            // MySQL tidak mendukung alter enum, pakai raw SQL
            $col = DB::select("SHOW COLUMNS FROM ujian_munaqosyahs WHERE Field = 'status'");
            if (! empty($col) && str_contains($col[0]->Type, 'enum')) {
                $enum = implode("','", self::STATUS_LENGKAP);
                DB::statement("ALTER TABLE ujian_munaqosyahs MODIFY status ENUM('{$enum}') NOT NULL DEFAULT 'draft'");
            }

            return;
        }

        // SQLite (testing): enum diwujudkan sebagai CHECK constraint — rebuild via change()
        Schema::table('ujian_munaqosyahs', function (Blueprint $table) {
            $table->enum('status', self::STATUS_LENGKAP)->default('draft')->change();
        });
    }

    public function down(): void
    {
        // Petakan dulu data dibatalkan -> ditolak agar enum lama valid
        DB::table('ujian_munaqosyahs')->where('status', 'dibatalkan')->update(['status' => 'ditolak']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            $col = DB::select("SHOW COLUMNS FROM ujian_munaqosyahs WHERE Field = 'status'");
            if (! empty($col) && str_contains($col[0]->Type, 'enum')) {
                $enum = implode("','", self::STATUS_LAMA);
                DB::statement("ALTER TABLE ujian_munaqosyahs MODIFY status ENUM('{$enum}') NOT NULL DEFAULT 'draft'");
            }

            return;
        }

        Schema::table('ujian_munaqosyahs', function (Blueprint $table) {
            $table->enum('status', self::STATUS_LAMA)->default('draft')->change();
        });
    }
};
