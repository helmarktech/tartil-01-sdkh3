<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sinkronisasi mata_pelajaran agar sama dengan jenis kelas.
     * BQ 1, BQ 2, BQ 3, BQ 4, Tartil, Tahfidz.
     */
    public function up(): void
    {
        DB::table('kelas')->update([
            'mata_pelajaran' => DB::raw('jenis'),
        ]);
    }

    public function down(): void
    {
        // Tidak bisa revert karena data lama tidak disimpan
    }
};
