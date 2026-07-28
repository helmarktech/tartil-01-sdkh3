<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semester_siswa', function (Blueprint $table) {
            if (!Schema::hasColumn('semester_siswa', 'kelas_reguler_id')) {
                $table->foreignId('kelas_reguler_id')->nullable()->after('kelas_id')->constrained('kelas_regulers')->onDelete('set null');
            }
            // Rename kelas_id to kelas_tartil_id for clarity
            if (Schema::hasColumn('semester_siswa', 'kelas_id')) {
                // Don't actually rename to avoid breaking existing code, just document that kelas_id = kelas tartil
            }
        });
    }

    public function down(): void
    {
        Schema::table('semester_siswa', function (Blueprint $table) {
            if (Schema::hasColumn('semester_siswa', 'kelas_reguler_id')) {
                $table->dropForeign(['kelas_reguler_id']);
                $table->dropColumn('kelas_reguler_id');
            }
        });
    }
};
