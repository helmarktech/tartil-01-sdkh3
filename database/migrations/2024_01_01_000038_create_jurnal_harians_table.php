<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jurnal_harians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('guru_tartils')->onDelete('cascade');
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->date('tanggal');
            // kehadiran: 0=Alpa, 1=Hadir, 2=Izin, 3=Sakit
            $table->tinyInteger('kehadiran')->default(1)->comment('0=Alpa,1=Hadir,2=Izin,3=Sakit');
            // penilaian: B=Baik, C=Cukup, K=Kurang
            $table->char('penilaian', 1)->nullable()->comment('B=Baik,C=Cukup,K=Kurang');
            $table->foreignId('surat_id')->nullable()->constrained('surats')->onDelete('set null');
            $table->integer('ayat_mulai')->unsigned()->nullable();
            $table->integer('ayat_selesai')->unsigned()->nullable();
            $table->string('catatan', 255)->nullable();
            $table->timestamps();

            // UNIQUE untuk dedup: 1 siswa hanya 1 entri per kelas per tanggal
            $table->unique(['kelas_id', 'tanggal', 'siswa_id'], 'idx_jurnal_unique_lookup');

            // Indexes untuk query cepat pada 7.000+ entri/hari
            $table->index(['siswa_id', 'tanggal'], 'idx_jurnal_siswa_tanggal');
            $table->index(['kelas_id', 'tanggal'], 'idx_jurnal_kelas_tanggal');
            $table->index('tanggal', 'idx_jurnal_tanggal');
            $table->index('semester_id', 'idx_jurnal_semester');
            $table->index(['semester_id', 'kelas_id', 'tanggal'], 'idx_jurnal_rekap');
        });

        // Partitioning opsional — hanya jika MySQL 8.0+ (bukan MariaDB)
        // Diabaikan jika tidak didukung, sistem tetap berjalan normal
        if (DB::getDriverName() === 'mysql') {
            try {
                $version = DB::selectOne("SELECT VERSION() as v")?->v ?? '';
                $isMySQL8 = str_contains($version, '8.');

                if ($isMySQL8) {
                    // MySQL 8.0+ supports function-based partitioning
                    $this->createPartitions();
                }
            } catch (\Exception $e) {
                // Partitioning tidak didukung — lanjut tanpa partitioning
                // Sistem tetap berjalan normal dengan indexes
            }
        }
    }

    private function createPartitions(): void
    {
        try {
            DB::statement(<<<'SQL'
                ALTER TABLE jurnal_harians
                PARTITION BY RANGE (YEAR(tanggal) * 100 + MONTH(tanggal)) (
                    PARTITION p202401 VALUES LESS THAN (202402),
                    PARTITION p202402 VALUES LESS THAN (202403),
                    PARTITION p202403 VALUES LESS THAN (202404),
                    PARTITION p202404 VALUES LESS THAN (202405),
                    PARTITION p202405 VALUES LESS THAN (202406),
                    PARTITION p202406 VALUES LESS THAN (202407),
                    PARTITION p202407 VALUES LESS THAN (202408),
                    PARTITION p202408 VALUES LESS THAN (202409),
                    PARTITION p202409 VALUES LESS THAN (202410),
                    PARTITION p202410 VALUES LESS THAN (202411),
                    PARTITION p202411 VALUES LESS THAN (202412),
                    PARTITION p202412 VALUES LESS THAN (202413),
                    PARTITION p202501 VALUES LESS THAN (202502),
                    PARTITION p202502 VALUES LESS THAN (202503),
                    PARTITION p202503 VALUES LESS THAN (202504),
                    PARTITION p202504 VALUES LESS THAN (202505),
                    PARTITION p202505 VALUES LESS THAN (202506),
                    PARTITION p202506 VALUES LESS THAN (202507),
                    PARTITION p202507 VALUES LESS THAN (202508),
                    PARTITION p202508 VALUES LESS THAN (202509),
                    PARTITION p202509 VALUES LESS THAN (202510),
                    PARTITION p202510 VALUES LESS THAN (202511),
                    PARTITION p202511 VALUES LESS THAN (202512),
                    PARTITION p202512 VALUES LESS THAN (202513),
                    PARTITION p202601 VALUES LESS THAN (202602),
                    PARTITION p202602 VALUES LESS THAN (202603),
                    PARTITION p202603 VALUES LESS THAN (202604),
                    PARTITION p202604 VALUES LESS THAN (202605),
                    PARTITION p202605 VALUES LESS THAN (202606),
                    PARTITION p202606 VALUES LESS THAN (202607),
                    PARTITION p202607 VALUES LESS THAN (202608),
                    PARTITION p202608 VALUES LESS THAN (202609),
                    PARTITION p202609 VALUES LESS THAN (202610),
                    PARTITION p202610 VALUES LESS THAN (202611),
                    PARTITION p202611 VALUES LESS THAN (202612),
                    PARTITION p202612 VALUES LESS THAN (202613),
                    PARTITION p202701 VALUES LESS THAN (202702),
                    PARTITION p202702 VALUES LESS THAN (202703),
                    PARTITION p202703 VALUES LESS THAN (202704),
                    PARTITION p202704 VALUES LESS THAN (202705),
                    PARTITION p202705 VALUES LESS THAN (202706),
                    PARTITION p202706 VALUES LESS THAN (202707),
                    PARTITION p202707 VALUES LESS THAN (202708),
                    PARTITION p202708 VALUES LESS THAN (202709),
                    PARTITION p202709 VALUES LESS THAN (202710),
                    PARTITION p202710 VALUES LESS THAN (202711),
                    PARTITION p202711 VALUES LESS THAN (202712),
                    PARTITION p202712 VALUES LESS THAN (202713),
                    PARTITION p202801 VALUES LESS THAN (202802),
                    PARTITION p202802 VALUES LESS THAN (202803),
                    PARTITION p202803 VALUES LESS THAN (202804),
                    PARTITION p202804 VALUES LESS THAN (202805),
                    PARTITION p202805 VALUES LESS THAN (202806),
                    PARTITION p202806 VALUES LESS THAN (202807),
                    PARTITION p202807 VALUES LESS THAN (202808),
                    PARTITION p202808 VALUES LESS THAN (202809),
                    PARTITION p202809 VALUES LESS THAN (202810),
                    PARTITION p202810 VALUES LESS THAN (202811),
                    PARTITION p202811 VALUES LESS THAN (202812),
                    PARTITION p202812 VALUES LESS THAN (202813),
                    PARTITION p202901 VALUES LESS THAN (202902),
                    PARTITION p202902 VALUES LESS THAN (202903),
                    PARTITION p202903 VALUES LESS THAN (202904),
                    PARTITION p202904 VALUES LESS THAN (202905),
                    PARTITION p202905 VALUES LESS THAN (202906),
                    PARTITION p202906 VALUES LESS THAN (202907),
                    PARTITION p202907 VALUES LESS THAN (202908),
                    PARTITION p202908 VALUES LESS THAN (202909),
                    PARTITION p202909 VALUES LESS THAN (202910),
                    PARTITION p202910 VALUES LESS THAN (202911),
                    PARTITION p202911 VALUES LESS THAN (202912),
                    PARTITION p202912 VALUES LESS THAN (202913),
                    PARTITION p203001 VALUES LESS THAN (203002),
                    PARTITION p203002 VALUES LESS THAN (203003),
                    PARTITION p203003 VALUES LESS THAN (203004),
                    PARTITION p203004 VALUES LESS THAN (203005),
                    PARTITION p203005 VALUES LESS THAN (203006),
                    PARTITION p203006 VALUES LESS THAN (203007),
                    PARTITION p203007 VALUES LESS THAN (203008),
                    PARTITION p203008 VALUES LESS THAN (203009),
                    PARTITION p203009 VALUES LESS THAN (203010),
                    PARTITION p203010 VALUES LESS THAN (203011),
                    PARTITION p203011 VALUES LESS THAN (203012),
                    PARTITION p203012 VALUES LESS THAN (203013),
                    PARTITION pmax VALUES LESS THAN MAXVALUE
                )
            SQL);
        } catch (\Exception $e) {
            // Partitioning gagal — lanjut tanpa partitioning
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jurnal_harians');
    }
};
