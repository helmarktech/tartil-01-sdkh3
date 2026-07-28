<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // STEP 1: Alter enum untuk sementara sertakan semua nilai lama + baru
        // (MySQL akan menolak kalau kita update ke nilai yang belum ada di enum)
        $col = DB::select("SHOW COLUMNS FROM munaqosyah_pendaftarans WHERE Field = 'status'");
        if (!empty($col) && str_contains($col[0]->Type, 'enum')) {
            DB::statement("ALTER TABLE munaqosyah_pendaftarans MODIFY status ENUM('pending','terdaftar','lulus','tidak_lulus','tidak_hadir','T','L','TL') NOT NULL DEFAULT 'pending'");
        }

        // STEP 2: Update data — map nilai lama ke baru
        DB::table('munaqosyah_pendaftarans')->where('status', 'terdaftar')->update(['status' => 'T']);
        DB::table('munaqosyah_pendaftarans')->where('status', 'lulus')->update(['status' => 'L']);
        DB::table('munaqosyah_pendaftarans')->where('status', 'tidak_lulus')->update(['status' => 'TL']);
        DB::table('munaqosyah_pendaftarans')->where('status', 'pending')->update(['status' => 'T']);
        DB::table('munaqosyah_pendaftarans')->where('status', 'tidak_hadir')->update(['status' => 'TL']);

        // STEP 3: Alter enum final — hanya T, L, TL
        DB::statement("ALTER TABLE munaqosyah_pendaftarans MODIFY status ENUM('T','L','TL') NOT NULL DEFAULT 'T'");
    }

    public function down(): void
    {
        // STEP 1: Sertakan semua nilai
        DB::statement("ALTER TABLE munaqosyah_pendaftarans MODIFY status ENUM('pending','terdaftar','lulus','tidak_lulus','tidak_hadir','T','L','TL') NOT NULL DEFAULT 'pending'");

        // STEP 2: Revert data
        DB::table('munaqosyah_pendaftarans')->where('status', 'T')->update(['status' => 'terdaftar']);
        DB::table('munaqosyah_pendaftarans')->where('status', 'L')->update(['status' => 'lulus']);
        DB::table('munaqosyah_pendaftarans')->where('status', 'TL')->update(['status' => 'tidak_lulus']);

        // STEP 3: Kembalikan enum ke semula
        DB::statement("ALTER TABLE munaqosyah_pendaftarans MODIFY status ENUM('pending','terdaftar','lulus','tidak_lulus','tidak_hadir') NOT NULL DEFAULT 'pending'");
    }
};
