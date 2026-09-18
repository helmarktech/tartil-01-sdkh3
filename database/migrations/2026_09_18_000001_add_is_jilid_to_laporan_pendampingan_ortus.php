<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menandai laporan pendampingan jenis jilid/bilqolam yang tidak
     * memerlukan data surat dan ayat.
     */
    public function up(): void
    {
        Schema::table('laporan_pendampingan_ortus', function (Blueprint $table) {
            $table->boolean('is_jilid')->default(false)->after('ayat_selesai')
                ->comment('Laporan jilid/bilqolam tanpa surat & ayat');
        });
    }

    public function down(): void
    {
        Schema::table('laporan_pendampingan_ortus', function (Blueprint $table) {
            $table->dropColumn('is_jilid');
        });
    }
};
