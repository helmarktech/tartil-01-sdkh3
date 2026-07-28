<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            if (Schema::hasColumn('siswas', 'kelas_reguler_id')) {
                $table->foreignId('kelas_reguler_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            // Tidak bisa rollback ke NOT NULL kalau sudah ada data null
        });
    }
};
