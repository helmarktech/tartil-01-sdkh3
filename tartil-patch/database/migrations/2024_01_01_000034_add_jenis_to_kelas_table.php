<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('kelas', 'jenis')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->enum('jenis', ['BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz'])->default('BQ 1')->after('nama');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kelas', 'jenis')) {
            Schema::table('kelas', function (Blueprint $table) {
                $table->dropColumn('jenis');
            });
        }
    }
};
