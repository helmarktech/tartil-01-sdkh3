<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * activity_logs.user_id menyimpan ID aktor dari guard manapun
     * (users untuk admin/guru, siswas untuk siswa — lihat user_type).
     * FK ke users.id keliru untuk aktor siswa → drop constraint-nya,
     * kolom & index dipertahankan.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            // SQLite (testing) mengabaikan dropForeign — tidak berpengaruh
            $table->dropForeign(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
