<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kop_surat_rapors', function (Blueprint $table) {
            $table->string('stempel_path')->nullable()->after('logo_path');
            $table->string('ttd_path')->nullable()->after('stempel_path');
        });
    }

    public function down(): void
    {
        Schema::table('kop_surat_rapors', function (Blueprint $table) {
            $table->dropColumn(['stempel_path', 'ttd_path']);
        });
    }
};
