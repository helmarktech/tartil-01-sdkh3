<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kop_surat_rapors')) {
            return;
        }

        // Tambah semester_id jika belum ada
        if (! Schema::hasColumn('kop_surat_rapors', 'semester_id')) {
            Schema::table('kop_surat_rapors', function (Blueprint $table) {
                $table->foreignId('semester_id')->nullable()->after('id')
                    ->constrained('semesters')->nullOnDelete();
            });
        }

        // Tambah is_default jika belum ada
        if (! Schema::hasColumn('kop_surat_rapors', 'is_default')) {
            Schema::table('kop_surat_rapors', function (Blueprint $table) {
                $table->boolean('is_default')->default(false)->after('semester_id')
                    ->comment('true = kop surat default/global aktif');
            });
        }

        // Tambah index jika belum ada
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            $indexExists = DB::select("SHOW INDEX FROM kop_surat_rapors WHERE Key_name = 'idx_kop_surat_semester'");
            if (empty($indexExists)) {
                Schema::table('kop_surat_rapors', function (Blueprint $table) {
                    $table->index(['semester_id'], 'idx_kop_surat_semester');
                });
            }
        } else {
            Schema::table('kop_surat_rapors', function (Blueprint $table) {
                $table->index(['semester_id'], 'idx_kop_surat_semester');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('kop_surat_rapors')) {
            return;
        }

        // Hapus foreign key dulu (cari nama constraint dari information_schema)
        if (Schema::hasColumn('kop_surat_rapors', 'semester_id')) {
            $fk = DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                 WHERE TABLE_NAME = 'kop_surat_rapors' 
                 AND COLUMN_NAME = 'semester_id' 
                 AND REFERENCED_TABLE_NAME IS NOT NULL"
            );

            if (! empty($fk)) {
                $fkName = $fk[0]->CONSTRAINT_NAME;
                DB::statement("ALTER TABLE kop_surat_rapors DROP FOREIGN KEY {$fkName}");
            }

            // Hapus index
            $indexExists = DB::select("SHOW INDEX FROM kop_surat_rapors WHERE Key_name = 'idx_kop_surat_semester'");
            if (! empty($indexExists)) {
                Schema::table('kop_surat_rapors', function (Blueprint $table) {
                    $table->dropIndex('idx_kop_surat_semester');
                });
            }

            // Hapus kolom
            Schema::table('kop_surat_rapors', function (Blueprint $table) {
                $table->dropColumn(['semester_id', 'is_default']);
            });
        } elseif (Schema::hasColumn('kop_surat_rapors', 'is_default')) {
            Schema::table('kop_surat_rapors', function (Blueprint $table) {
                $table->dropColumn('is_default');
            });
        }
    }
};
