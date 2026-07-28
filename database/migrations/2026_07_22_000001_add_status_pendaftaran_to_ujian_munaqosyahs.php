<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom status_pendaftaran ke ujian_munaqosyahs.
     * Terpisah dari kolom status (approval workflow).
     *
     * buka  = pendaftaran bisa dilakukan
     * tutup = pendaftaran ditutup, ujian di-rekap
     */
    public function up(): void
    {
        if (Schema::hasColumn('ujian_munaqosyahs', 'status_pendaftaran')) {
            return;
        }

        Schema::table('ujian_munaqosyahs', function (Blueprint $table) {
            $table->enum('status_pendaftaran', ['buka', 'tutup'])->default('buka')->after('status');
        });

        // Update record existing: set semua jadi 'buka'
        \App\Models\UjianMunaqosyah::query()->update(['status_pendaftaran' => 'buka']);
    }

    public function down(): void
    {
        if (!Schema::hasColumn('ujian_munaqosyahs', 'status_pendaftaran')) {
            return;
        }

        Schema::table('ujian_munaqosyahs', function (Blueprint $table) {
            $table->dropColumn('status_pendaftaran');
        });
    }
};
