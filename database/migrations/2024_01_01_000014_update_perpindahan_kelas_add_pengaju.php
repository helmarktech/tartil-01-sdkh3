<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('perpindahan_kelas', function (Blueprint $table) {
            $table->foreignId('diajukan_oleh')->nullable()->after('siswa_id')->constrained('users')->nullOnDelete();
            $table->foreignId('guru_tujuan_id')->nullable()->after('kelas_baru_id')->constrained('guru_tartils')->nullOnDelete();
            $table->enum('jenis', ['tartil', 'reguler'])->default('tartil')->after('guru_tujuan_id');
        });
    }

    public function down(): void
    {
        Schema::table('perpindahan_kelas', function (Blueprint $table) {
            $table->dropForeign(['diajukan_oleh']);
            $table->dropForeign(['guru_tujuan_id']);
            $table->dropColumn(['diajukan_oleh', 'guru_tujuan_id', 'jenis']);
        });
    }
};
