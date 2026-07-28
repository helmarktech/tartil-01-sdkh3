<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Helper: cek apakah index sudah ada
        $indexExists = function (string $table, string $index): bool {
            return !empty(\DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$index}'"));
        };

        // Index untuk jurnal_harians — query paling sering
        if (Schema::hasTable('jurnal_harians')) {
            Schema::table('jurnal_harians', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('jurnal_harians', 'idx_jurnal_kelas_sem_tgl'))
                    $table->index(['kelas_id', 'semester_id', 'tanggal'], 'idx_jurnal_kelas_sem_tgl');
                if (!$indexExists('jurnal_harians', 'idx_jurnal_siswa_sem'))
                    $table->index(['siswa_id', 'semester_id'], 'idx_jurnal_siswa_sem');
                if (!$indexExists('jurnal_harians', 'idx_jurnal_sem_kelas'))
                    $table->index(['semester_id', 'kelas_id'], 'idx_jurnal_sem_kelas');
            });
        }

        // Index untuk rekap_jurnal_bulanans
        if (Schema::hasTable('rekap_jurnal_bulanans')) {
            Schema::table('rekap_jurnal_bulanans', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('rekap_jurnal_bulanans', 'idx_rekap_sem_kelas_siswa'))
                    $table->index(['semester_id', 'kelas_id', 'siswa_id'], 'idx_rekap_sem_kelas_siswa');
                if (!$indexExists('rekap_jurnal_bulanans', 'idx_rekap_siswa_sem'))
                    $table->index(['siswa_id', 'semester_id'], 'idx_rekap_siswa_sem');
            });
        }

        // Index untuk munaqosyah_pendaftarans
        if (Schema::hasTable('munaqosyah_pendaftarans')) {
            Schema::table('munaqosyah_pendaftarans', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('munaqosyah_pendaftarans', 'idx_munaqosyah_status'))
                    $table->index(['munaqosyah_id', 'status'], 'idx_munaqosyah_status');
                if (!$indexExists('munaqosyah_pendaftarans', 'idx_munaqosyah_siswa'))
                    $table->index(['siswa_id', 'munaqosyah_id'], 'idx_munaqosyah_siswa');
            });
        }

        // Index untuk penilaian_rapor_nilais
        if (Schema::hasTable('penilaian_rapor_nilais')) {
            Schema::table('penilaian_rapor_nilais', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('penilaian_rapor_nilais', 'idx_nilai_penilaian_siswa'))
                    $table->index(['penilaian_id', 'siswa_id'], 'idx_nilai_penilaian_siswa');
            });
        }

        // Index untuk semester_siswa
        if (Schema::hasTable('semester_siswa')) {
            Schema::table('semester_siswa', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('semester_siswa', 'idx_ss_sem_kelas'))
                    $table->index(['semester_id', 'kelas_id'], 'idx_ss_sem_kelas');
            });
        }
    }

    public function down(): void
    {
        // Idempotent: tiap drop dibungkus try-catch karena index mungkin
        // sudah di-drop oleh migrasi lain, atau diperlukan FK constraint

        if (Schema::hasTable('jurnal_harians')) {
            try { Schema::table('jurnal_harians', fn(Blueprint $t) => $t->dropIndex('idx_jurnal_kelas_sem_tgl')); } catch (\Throwable $e) {}
            try { Schema::table('jurnal_harians', fn(Blueprint $t) => $t->dropIndex('idx_jurnal_siswa_sem')); } catch (\Throwable $e) {}
            try { Schema::table('jurnal_harians', fn(Blueprint $t) => $t->dropIndex('idx_jurnal_sem_kelas')); } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('rekap_jurnal_bulanans')) {
            try { Schema::table('rekap_jurnal_bulanans', fn(Blueprint $t) => $t->dropIndex('idx_rekap_sem_kelas_siswa')); } catch (\Throwable $e) {}
            try { Schema::table('rekap_jurnal_bulanans', fn(Blueprint $t) => $t->dropIndex('idx_rekap_siswa_sem')); } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('munaqosyah_pendaftarans')) {
            // Hapus FK dulu baru index (cari dari information_schema)
            $fks = \DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = 'munaqosyah_pendaftarans' 
                AND (COLUMN_NAME = 'munaqosyah_id' OR COLUMN_NAME = 'status') 
                AND REFERENCED_TABLE_NAME IS NOT NULL");
            foreach ($fks as $fk) {
                try { \DB::statement("ALTER TABLE munaqosyah_pendaftarans DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}"); } catch (\Throwable $e) {}
            }
            try { Schema::table('munaqosyah_pendaftarans', fn(Blueprint $t) => $t->dropIndex('idx_munaqosyah_status')); } catch (\Throwable $e) {}
            try { Schema::table('munaqosyah_pendaftarans', fn(Blueprint $t) => $t->dropIndex('idx_munaqosyah_siswa')); } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('penilaian_rapor_nilais')) {
            try { Schema::table('penilaian_rapor_nilais', fn(Blueprint $t) => $t->dropIndex('idx_nilai_penilaian_siswa')); } catch (\Throwable $e) {}
        }

        if (Schema::hasTable('semester_siswa')) {
            try { Schema::table('semester_siswa', fn(Blueprint $t) => $t->dropIndex('idx_ss_sem_kelas')); } catch (\Throwable $e) {}
        }
    }
};
