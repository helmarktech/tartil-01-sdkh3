<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah status siswa: aktif | lulus | mutasi_keluar
        // Catatan: 'nonaktif' lama diubah menjadi 'lulus' atau 'mutasi_keluar'
        if (Schema::hasColumn('siswas', 'status')) {
            // Untuk existing data: nonaktif → mutasi_keluar (default)
            \DB::table('siswas')->where('status', 'nonaktif')->update(['status' => 'mutasi_keluar']);

            Schema::table('siswas', function (Blueprint $table) {
                $table->enum('status', ['aktif', 'lulus', 'mutasi_keluar'])->default('aktif')->change();
            });
        }

        // Tambah keterangan_status untuk detail (opsional)
        if (!Schema::hasColumn('siswas', 'keterangan_status')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->string('keterangan_status', 100)->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('siswas', 'status')) {
            \DB::table('siswas')->whereIn('status', ['lulus', 'mutasi_keluar'])->update(['status' => 'nonaktif']);

            Schema::table('siswas', function (Blueprint $table) {
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->change();
            });
        }

        if (Schema::hasColumn('siswas', 'keterangan_status')) {
            Schema::table('siswas', function (Blueprint $table) {
                $table->dropColumn('keterangan_status');
            });
        }
    }
};
