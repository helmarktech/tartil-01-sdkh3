<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kenaikan_kelas_regulers', function (Blueprint $table) {
            // Kategori: naik = naik kelas, lulus = kelas 6 selesai, mutasi = keluar sekolah
            if (!Schema::hasColumn('kenaikan_kelas_regulers', 'kategori')) {
                $table->enum('kategori', ['naik', 'lulus', 'mutasi'])->default('naik')->after('tahun_ajaran');
            }
            // Untuk kelas 6 yang lulus, kelas_baru = null (tidak ada kelas baru)
            if (Schema::hasColumn('kenaikan_kelas_regulers', 'kelas_reguler_baru_id')) {
                $table->foreignId('kelas_reguler_baru_id')->nullable()->change();
            }
        });

        // Untuk existing data, set kategori = 'naik' (default)
        \DB::table('kenaikan_kelas_regulers')->whereNull('kategori')->update(['kategori' => 'naik']);
    }

    public function down(): void
    {
        Schema::table('kenaikan_kelas_regulers', function (Blueprint $table) {
            if (Schema::hasColumn('kenaikan_kelas_regulers', 'kategori')) {
                $table->dropColumn('kategori');
            }
        });
    }
};
