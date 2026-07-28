<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. jurnal_harians.semester_id
        Schema::table('jurnal_harians', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('semesters')->restrictOnDelete();
        });

        // 2. rekap_jurnal_bulanans.semester_id
        Schema::table('rekap_jurnal_bulanans', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('semesters')->restrictOnDelete();
        });

        // 3. semester_kelas.semester_id (sudah pakai onDelete('cascade') di migration)
        Schema::table('semester_kelas', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('semesters')->restrictOnDelete();
        });

        // 4. semester_siswa.semester_id
        Schema::table('semester_siswa', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('semesters')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Revert ke cascade
        Schema::table('jurnal_harians', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
        });

        Schema::table('rekap_jurnal_bulanans', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
        });

        Schema::table('semester_kelas', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
        });

        Schema::table('semester_siswa', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('cascade');
        });
    }
};
