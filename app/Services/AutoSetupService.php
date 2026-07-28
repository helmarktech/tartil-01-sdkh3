<?php

namespace App\Services;

use App\Models\Guru;
use App\Models\JurnalHarian;
use App\Models\Kelas;
use App\Models\KopSuratRapor;
use App\Models\Siswa;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AutoSetupService
{
    /**
     * Cek apakah sistem sudah fully setup.
     */
    public static function isFullySetup(): bool
    {
        return cache()->remember('system.setup.complete', 3600, function () {
            return static::checkMigrationTable()
                && static::checkR2CacheTable()
                && static::checkActivityLogTable()
                && static::checkPerformanceIndexes()
                && static::checkQueueTable()
                && static::checkKopSurat();
        });
    }

    /**
     * Jalankan semua setup steps.
     */
    public static function runAllSetup(): array
    {
        $results = [];

        $results[] = static::step('Menjalankan migrasi database', function () {
            Artisan::call('migrate', ['--force' => true]);

            return 'Migrasi berhasil: '.trim(Artisan::output());
        });

        $results[] = static::step('Membuat queue table', function () {
            if (! Schema::hasTable('jobs')) {
                Artisan::call('queue:table');
                Artisan::call('migrate', ['--force' => true]);

                return 'Queue table dibuat';
            }

            return 'Queue table sudah ada';
        });

        $results[] = static::step('Menambahkan performance indexes', function () {
            return static::addPerformanceIndexes();
        });

        $results[] = static::step('Membuat R2 cache table', function () {
            return static::createR2CacheTable();
        });

        $results[] = static::step('Membuat activity logs table', function () {
            return static::createActivityLogTable();
        });

        $results[] = static::step('Membuat kop surat default', function () {
            return static::createDefaultKopSurat();
        });

        $results[] = static::step('Menjalankan R2 precalculate pertama', function () {
            Artisan::call('r2:precalculate');

            return 'R2 precalculate selesai: '.trim(Artisan::output());
        });

        $results[] = static::step('Clear dan cache ulang', function () {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            return 'Config, route, view dicache';
        });

        cache()->forget('system.setup.complete');

        return $results;
    }

    /**
     * Run single step with error handling.
     */
    protected static function step(string $label, callable $callback): array
    {
        try {
            $message = $callback();

            return ['status' => 'success', 'label' => $label, 'message' => $message];
        } catch (\Throwable $e) {
            return ['status' => 'error', 'label' => $label, 'message' => $e->getMessage()];
        }
    }

    /**
     * Cek tabel migrasi.
     */
    public static function checkMigrationTable(): bool
    {
        return Schema::hasTable('migrations');
    }

    /**
     * Cek R2 cache table.
     */
    public static function checkR2CacheTable(): bool
    {
        return Schema::hasTable('rekap_r2_akhirs');
    }

    /**
     * Cek activity log table.
     */
    public static function checkActivityLogTable(): bool
    {
        return Schema::hasTable('activity_logs');
    }

    /**
     * Cek performance indexes.
     */
    public static function checkPerformanceIndexes(): bool
    {
        if (! Schema::hasTable('jurnal_harians')) {
            return false;
        }

        $indexes = DB::select('SHOW INDEX FROM jurnal_harians');
        $indexNames = array_column($indexes, 'Key_name');

        return in_array('idx_jurnal_siswa_bulan', $indexNames);
    }

    /**
     * Cek queue table.
     */
    public static function checkQueueTable(): bool
    {
        return Schema::hasTable('jobs');
    }

    /**
     * Cek kop surat.
     */
    public static function checkKopSurat(): bool
    {
        try {
            return KopSuratRapor::count() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Tambah performance indexes via raw SQL (jika migration belum ada).
     */
    protected static function addPerformanceIndexes(): string
    {
        $indexes = [
            'jurnal_harians' => [
                'idx_jurnal_siswa_tanggal' => 'siswa_id, tanggal',
            ],
            'rekap_jurnal_bulanans' => [
                'idx_rekap_siswa_bulan' => 'siswa_id, bulan',
            ],
            'penilaian_rapor_nilais' => [
                'idx_nilai_siswa_indikator' => 'siswa_id, indikator_penilaian_id',
            ],
            'semester_siswa' => [
                'idx_semester_siswa' => 'semester_id, siswa_id',
            ],
        ];

        $added = 0;
        foreach ($indexes as $table => $tableIndexes) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $existing = array_column(DB::select("SHOW INDEX FROM {$table}"), 'Key_name');

            foreach ($tableIndexes as $name => $columns) {
                if (! in_array($name, $existing)) {
                    try {
                        DB::statement("CREATE INDEX {$name} ON {$table} ({$columns})");
                        $added++;
                    } catch (\Throwable $e) {
                        // Index mungkin sudah ada dengan nama berbeda
                    }
                }
            }
        }

        return $added > 0 ? "{$added} index ditambahkan" : 'Semua index sudah ada';
    }

    /**
     * Buat R2 cache table jika belum ada.
     */
    protected static function createR2CacheTable(): string
    {
        if (Schema::hasTable('rekap_r2_akhirs')) {
            return 'Table rekap_r2_akhirs sudah ada';
        }

        Schema::create('rekap_r2_akhirs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('semester_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('siswa_id');
            $table->unsignedTinyInteger('r2_harian')->default(0);
            $table->unsignedTinyInteger('r2_penilaian')->default(0);
            $table->unsignedTinyInteger('r2_akhir')->default(0);
            $table->unsignedSmallInteger('jumlah_indikator')->default(0);
            $table->unsignedSmallInteger('jumlah_terisi')->default(0);
            $table->boolean('is_mutasi')->default(false);
            $table->timestamp('last_calculated')->useCurrent();
            $table->timestamps();

            $table->unique(['semester_id', 'kelas_id', 'siswa_id'], 'unique_r2_siswa');
            $table->index('siswa_id');
        });

        return 'Table rekap_r2_akhirs berhasil dibuat';
    }

    /**
     * Buat activity logs table jika belum ada.
     */
    protected static function createActivityLogTable(): string
    {
        if (Schema::hasTable('activity_logs')) {
            return 'Table activity_logs sudah ada';
        }

        Schema::create('activity_logs', function ($table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_type', 20)->nullable();
            $table->string('action', 50);
            $table->string('table_name', 50)->nullable();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['table_name', 'record_id']);
            $table->index('user_id');
            $table->index('created_at');
        });

        return 'Table activity_logs berhasil dibuat';
    }

    /**
     * Buat default kop surat.
     */
    protected static function createDefaultKopSurat(): string
    {
        try {
            if (KopSuratRapor::count() > 0) {
                return 'Kop surat sudah ada';
            }

            KopSuratRapor::create([
                'judul' => 'LAPORAN HASIL PEMBELAJARAN TARTIL',
                'sub_judul' => 'MADRASAH DINIYAH / TPQ',
                'nama_sekolah' => 'Nama Sekolah Anda',
                'alamat' => 'Alamat Lengkap Sekolah',
                'telepon' => '(021) 1234567',
                'email' => 'sekolah@email.com',
                'website' => 'www.sekolah.sch.id',
                'tahun_ajaran' => date('Y').'/'.(date('Y') + 1),
                'catatan_kaki' => 'Laporan ini merupakan hasil penilaian pembelajaran tartil selama satu semester.',
                'kepala_sekolah' => 'Nama Kepala Sekolah',
                'nip_kepala_sekolah' => 'NIP. 1234567890',
            ]);

            return 'Kop surat default dibuat';
        } catch (\Throwable $e) {
            return 'Error kop surat: '.$e->getMessage();
        }
    }

    /**
     * Get system status untuk dashboard.
     */
    public static function getSystemStatus(): array
    {
        $status = [];

        try {
            $dbSize = DB::selectOne('
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size
                FROM information_schema.tables
                WHERE table_schema = DATABASE()
            ');
            $status['db_size_mb'] = $dbSize->size ?? 0;
        } catch (\Throwable $e) {
            $status['db_size_mb'] = 0;
        }

        $status['tables'] = [
            'migrations' => Schema::hasTable('migrations'),
            'jurnal_harians' => Schema::hasTable('jurnal_harians'),
            'rekap_r2_akhirs' => static::checkR2CacheTable(),
            'activity_logs' => static::checkActivityLogTable(),
            'jobs' => static::checkQueueTable(),
            'failed_jobs' => Schema::hasTable('failed_jobs'),
        ];

        $status['indexes'] = static::checkPerformanceIndexes();
        $status['kop_surat'] = static::checkKopSurat();
        $status['fully_setup'] = static::isFullySetup();

        // Statistik data
        try {
            $status['stats'] = [
                'total_siswa' => Siswa::count(),
                'total_guru' => Guru::count(),
                'total_kelas' => Kelas::count(),
                'total_jurnal' => JurnalHarian::count(),
                'total_jurnal_bulanan' => DB::table('rekap_jurnal_bulanans')->count(),
                'r2_cached' => DB::table('rekap_r2_akhirs')->count(),
                'activity_logs' => DB::table('activity_logs')->count(),
            ];
        } catch (\Throwable $e) {
            $status['stats'] = [];
        }

        return $status;
    }

    /**
     * Reset cache R2 (force recalculate).
     */
    public static function resetR2Cache(): string
    {
        try {
            DB::table('rekap_r2_akhirs')->truncate();
            Artisan::call('r2:precalculate');

            return 'R2 cache di-reset dan dihitung ulang: '.trim(Artisan::output());
        } catch (\Throwable $e) {
            return 'Error: '.$e->getMessage();
        }
    }

    /**
     * Jalankan artisan command dari web.
     */
    public static function runArtisan(string $command, array $options = []): array
    {
        try {
            $exitCode = Artisan::call($command, $options);
            $output = trim(Artisan::output());

            return [
                'status' => $exitCode === 0 ? 'success' : 'warning',
                'exit_code' => $exitCode,
                'output' => $output ?: 'Command executed successfully',
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 'error',
                'exit_code' => -1,
                'output' => $e->getMessage(),
            ];
        }
    }
}
