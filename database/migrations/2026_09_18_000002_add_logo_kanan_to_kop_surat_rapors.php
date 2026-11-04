<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Logo kanan pada kop surat rapor (logo_path tetap sebagai logo kiri).
     */
    public function up(): void
    {
        Schema::table('kop_surat_rapors', function (Blueprint $table) {
            $table->string('logo_kanan_path')->nullable()->after('logo_path');
        });
    }

    public function down(): void
    {
        Schema::table('kop_surat_rapors', function (Blueprint $table) {
            $table->dropColumn('logo_kanan_path');
        });
    }
};
