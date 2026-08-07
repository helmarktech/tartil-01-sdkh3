<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom tanggal konfirmasi orang tua untuk setoran hafalan.
     */
    public function up(): void
    {
        if (Schema::hasColumn('hafalan_tahfidzs', 'dikonfirmasi_orang_tua_at')) {
            return;
        }

        Schema::table('hafalan_tahfidzs', function (Blueprint $table) {
            $table->dateTime('dikonfirmasi_orang_tua_at')->nullable()->after('tanggal_hafalan')->comment('Tanggal & waktu orang tua mengkonfirmasi setoran');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('hafalan_tahfidzs', 'dikonfirmasi_orang_tua_at')) {
            Schema::table('hafalan_tahfidzs', function (Blueprint $table) {
                $table->dropColumn('dikonfirmasi_orang_tua_at');
            });
        }
    }
};
