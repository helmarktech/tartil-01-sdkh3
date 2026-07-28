<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit log untuk melacak setiap proses lock snapshot semester.
     * Berguna untuk debugging dan verifikasi data oleh pimpinan.
     */
    public function up(): void
    {
        Schema::create('semester_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->string('tipe', 30);              // 'jurnal' | 'munaqosyah' | 'riwayat' | 'r2' | 'kop_surat'
            $table->string('aksi', 20);              // 'snapshot' | 'retroactive' | 'recalculate'
            $table->integer('jumlah_record')->default(0);   // Berapa record yang di-lock
            $table->text('detail')->nullable();      // JSON detail tambahan
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // Siapa yang menutup
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('locked_at');
            $table->timestamps();

            $table->index(['semester_id', 'tipe']);
            $table->index('locked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semester_audit_logs');
    }
};
