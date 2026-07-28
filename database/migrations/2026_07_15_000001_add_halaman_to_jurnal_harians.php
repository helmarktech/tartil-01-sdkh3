<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jurnal_harians', function (Blueprint $table) {
            $table->string('halaman', 50)->nullable()->after('ayat_selesai')->comment('Contoh: Juz 1 hal 23-25');
        });
    }

    public function down(): void
    {
        Schema::table('jurnal_harians', function (Blueprint $table) {
            $table->dropColumn('halaman');
        });
    }
};
