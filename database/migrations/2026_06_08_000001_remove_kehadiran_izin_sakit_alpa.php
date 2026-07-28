<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus kolom kehadiran dari jurnal_harians (sistem hanya punya B, C, K — tidak ada izin/sakit/alpa)
        Schema::table('jurnal_harians', function (Blueprint $table) {
            $table->dropColumn('kehadiran');
        });

        // Hapus kolom total_izin, total_sakit, total_alpa dari rekap_jurnal_bulanans
        Schema::table('rekap_jurnal_bulanans', function (Blueprint $table) {
            $table->dropColumn(['total_izin', 'total_sakit', 'total_alpa']);
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_harians', function (Blueprint $table) {
            $table->tinyInteger('kehadiran')->default(1)->comment('0=Alpa,1=Hadir,2=Izin,3=Sakit')->after('penilaian');
        });

        Schema::table('rekap_jurnal_bulanans', function (Blueprint $table) {
            $table->smallInteger('total_izin')->unsigned()->default(0)->after('total_hadir');
            $table->smallInteger('total_sakit')->unsigned()->default(0)->after('total_izin');
            $table->smallInteger('total_alpa')->unsigned()->default(0)->after('total_sakit');
        });
    }
};
