<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Migration ini hanya untuk UPGRADE existing database.
        // Di fresh install, tabel guru_tartils sudah dibuat oleh 000003,
        // jadi skip semua operasi rename.
        if (!Schema::hasTable('gurus')) {
            // Fresh install: gurus tidak ada berarti guru_tartils sudah dibuat oleh 000003
            // Tidak perlu apa-apa
            return;
        }

        // ============================================
        // STEP 1: Drop all FK constraints referencing gurus
        // ============================================

        // Drop FK dengan raw SQL + try-catch (lebih reliable dari Blueprint::dropForeign)
        $dropFk = function (string $table, string $column): void {
            $fks = \DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$column}' AND REFERENCED_TABLE_NAME IS NOT NULL");
            foreach ($fks as $fk) {
                try { \DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}"); } catch (\Throwable $e) {}
            }
        };

        $dropFk('kelas', 'guru_id');
        $dropFk('users', 'guru_id');
        $dropFk('jurnals', 'guru_id');
        $dropFk('perpindahan_kelas', 'guru_tujuan_id');
        $dropFk('perpindahan_kelas', 'pengaju_id');
        $dropFk('perpindahan_kelas', 'approved_by');
        $dropFk('ujian_munaqosyahs', 'diajukan_oleh');
        $dropFk('kelas_regulers', 'guru_pengampu_id');

        // ============================================
        // STEP 2: Rename gurus → guru_tartils
        // ============================================
        Schema::rename('gurus', 'guru_tartils');

        // ============================================
        // STEP 3: Recreate FKs referencing guru_tartils
        // ============================================

        Schema::table('kelas', function (Blueprint $table) {
            if (Schema::hasColumn('kelas', 'guru_id')) {
                $table->foreign('guru_id')->references('id')->on('guru_tartils')->nullOnDelete();
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'guru_id')) {
                $table->foreign('guru_id')->references('id')->on('guru_tartils')->nullOnDelete();
            }
        });

        Schema::table('jurnals', function (Blueprint $table) {
            if (Schema::hasColumn('jurnals', 'guru_id')) {
                $table->foreign('guru_id')->references('id')->on('guru_tartils')->cascadeOnDelete();
            }
        });

        Schema::table('perpindahan_kelas', function (Blueprint $table) {
            if (Schema::hasColumn('perpindahan_kelas', 'guru_tujuan_id')) {
                $table->foreign('guru_tujuan_id')->references('id')->on('guru_tartils')->nullOnDelete();
            }
        });

        Schema::table('ujian_munaqosyahs', function (Blueprint $table) {
            if (Schema::hasColumn('ujian_munaqosyahs', 'diajukan_oleh')) {
                $table->foreign('diajukan_oleh')->references('id')->on('guru_tartils')->nullOnDelete();
            }
        });

        // ============================================
        // STEP 4: Create guru_regulers table
        // ============================================
        if (!Schema::hasTable('guru_regulers')) {
            Schema::create('guru_regulers', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 100);
                $table->string('nip', 30)->nullable()->unique();
                $table->string('email', 100)->unique();
                $table->string('no_hp', 15);
                $table->enum('jenis_kelamin', ['L', 'P']);
                $table->text('alamat')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->softDeletes();
                $table->timestamps();
            });
        }

        // ============================================
        // STEP 5: Update kelas_regulers FK to guru_regulers
        // ============================================
        Schema::table('kelas_regulers', function (Blueprint $table) {
            if (Schema::hasColumn('kelas_regulers', 'guru_pengampu_id')) {
                $table->foreign('guru_pengampu_id')->references('id')->on('guru_regulers')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('guru_tartils')) {
            return;
        }

        // Drop FK dengan raw SQL + try-catch (lebih reliable dari Blueprint::dropForeign)
        $dropFk = function (string $table, string $column): void {
            $fks = \DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$column}' AND REFERENCED_TABLE_NAME IS NOT NULL");
            foreach ($fks as $fk) {
                try { \DB::statement("ALTER TABLE {$table} DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}"); } catch (\Throwable $e) {}
            }
        };

        $dropFk('kelas', 'guru_id');
        $dropFk('users', 'guru_id');
        $dropFk('jurnals', 'guru_id');
        $dropFk('perpindahan_kelas', 'guru_tujuan_id');
        $dropFk('perpindahan_kelas', 'pengaju_id');
        $dropFk('perpindahan_kelas', 'approved_by');
        $dropFk('ujian_munaqosyahs', 'diajukan_oleh');
        $dropFk('kelas_regulers', 'guru_pengampu_id');

        Schema::dropIfExists('guru_regulers');
        Schema::rename('guru_tartils', 'gurus');

        Schema::table('kelas', function (Blueprint $table) {
            $table->foreign('guru_id')->references('id')->on('gurus')->nullOnDelete();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('guru_id')->references('id')->on('gurus')->nullOnDelete();
        });
        Schema::table('jurnals', function (Blueprint $table) {
            $table->foreign('guru_id')->references('id')->on('gurus')->cascadeOnDelete();
        });
        Schema::table('perpindahan_kelas', function (Blueprint $table) {
            $table->foreign('guru_tujuan_id')->references('id')->on('gurus')->nullOnDelete();
        });
        Schema::table('ujian_munaqosyahs', function (Blueprint $table) {
            $table->foreign('diajukan_oleh')->references('id')->on('gurus')->nullOnDelete();
        });
    }
};
