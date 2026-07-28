<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 20)->unique(); // 2025/2026
            $table->date('tanggal_mulai'); // 1 Juli
            $table->date('tanggal_selesai'); // 30 Juni
            $table->enum('status', ['aktif', 'ditutup'])->default('aktif');
            $table->timestamps();
        });

        // Relasi semesters → tahun_ajaran
        if (Schema::hasTable('semesters')) {
            Schema::table('semesters', function (Blueprint $table) {
                if (!Schema::hasColumn('semesters', 'tahun_ajaran_id')) {
                    $table->foreignId('tahun_ajaran_id')->nullable()->after('id')->constrained('tahun_ajaran')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('semesters')) {
            Schema::table('semesters', function (Blueprint $table) {
                if (Schema::hasColumn('semesters', 'tahun_ajaran_id')) {
                    $table->dropForeign(['tahun_ajaran_id']);
                    $table->dropColumn('tahun_ajaran_id');
                }
            });
        }
        Schema::dropIfExists('tahun_ajaran');
    }
};
