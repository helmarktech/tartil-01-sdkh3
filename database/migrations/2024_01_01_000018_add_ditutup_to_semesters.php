<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('semesters', function (Blueprint $table) {
            // Kolom status ditangani oleh migration 000022
            // Index untuk validasi TA + jenis
            $table->index(['tahun_ajaran', 'jenis']);
        });
    }

    public function down(): void
    {
        // Idempotent: index mungkin diperlukan FK constraint dari tabel lain
        try {
            Schema::table('semesters', function (Blueprint $table) {
                $table->dropIndex(['tahun_ajaran', 'jenis']);
            });
        } catch (\Throwable $e) {
            // Index tidak bisa di-drop karena dibutuhkan FK — skip
        }
    }
};
