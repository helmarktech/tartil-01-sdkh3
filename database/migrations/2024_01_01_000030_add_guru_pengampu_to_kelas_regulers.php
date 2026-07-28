<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas_regulers', function (Blueprint $table) {
            if (!Schema::hasColumn('kelas_regulers', 'guru_pengampu_id')) {
                $table->foreignId('guru_pengampu_id')->nullable()->after('tingkat')->constrained('guru_regulers')->nullOnDelete();
            }
            if (!Schema::hasColumn('kelas_regulers', 'keterangan')) {
                $table->string('keterangan', 255)->nullable()->after('guru_pengampu_id');
            }
        });
    }

    public function down(): void
    {
        // noop
    }
};
