<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kop_surat_rapors', function (Blueprint $table) {
            $table->date('tanggal_cetak')->nullable()->after('tahun_ajaran');
        });
    }

    public function down(): void
    {
        Schema::table('kop_surat_rapors', function (Blueprint $table) {
            $table->dropColumn('tanggal_cetak');
        });
    }
};
