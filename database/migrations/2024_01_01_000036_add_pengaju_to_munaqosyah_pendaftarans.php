<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('munaqosyah_pendaftarans', 'diajukan_oleh')) {
            Schema::table('munaqosyah_pendaftarans', function (Blueprint $table) {
                $table->foreignId('diajukan_oleh')->nullable()->after('siswa_id')->constrained('users')->nullOnDelete();
                $table->string('pengaju_type', 20)->nullable()->after('diajukan_oleh'); // 'admin' atau 'guru'
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('munaqosyah_pendaftarans', 'diajukan_oleh')) {
            Schema::table('munaqosyah_pendaftarans', function (Blueprint $table) {
                $table->dropForeign(['diajukan_oleh']);
                $table->dropColumn(['diajukan_oleh', 'pengaju_type']);
            });
        }
    }
};
