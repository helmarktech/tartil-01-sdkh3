<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Semester;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\RekapR2Akhir;
use App\Models\RekapJurnalSemester;
use App\Models\RekapMunaqosyahSemester;
use App\Models\RekapRiwayatSemester;
use App\Models\KopSuratRapor;
use App\Models\SemesterAuditLog;

class SemesterRetroactiveLock extends Command
{
    protected $signature = 'semester:retroactive-lock 
                            {semester? : ID semester yang akan di-lock}
                            {--all : Lock semua semester yang sudah ditutup}';

    protected $description = 'Mengunci (snapshot) data semester yang sudah ditutup sebelum sistem snapshot ada.';

    public function handle()
    {
        $semesterId = $this->argument('semester');
        $lockAll = $this->option('all');

        if (!$semesterId && !$lockAll) {
            $this->error('Gunakan: php artisan semester:retroactive-lock {semester_id} atau --all');
            $this->info('Semester yang sudah ditutup:');
            $semesters = Semester::where('status', 'ditutup')->orderBy('tanggal_mulai', 'desc')->get();
            foreach ($semesters as $s) {
                $hasR2 = RekapR2Akhir::where('semester_id', $s->id)->count();
                $hasJurnal = RekapJurnalSemester::where('semester_id', $s->id)->count();
                $status = ($hasR2 > 0 && $hasJurnal > 0) ? '✅ Lengkap' : (($hasR2 > 0) ? '⚠️ R2 only' : '❌ Belum lock');
                $this->info("  ID {$s->id}: {$s->nama} — {$status}");
            }
            return 1;
        }

        if ($lockAll) {
            $semesters = Semester::where('status', 'ditutup')->orderBy('tanggal_mulai')->get();
        } else {
            $semesters = Semester::where('id', $semesterId)->get();
        }

        foreach ($semesters as $semester) {
            $this->info("\n══════════════════════════════════════════");
            $this->info("Processing: {$semester->nama} (ID: {$semester->id})");
            $this->info("══════════════════════════════════════════");

            if ($semester->status !== 'ditutup') {
                $this->warn("  Semester belum ditutup. Lewati.");
                continue;
            }

            // Cek apakah sudah pernah di-lock
            $existingJurnal = RekapJurnalSemester::where('semester_id', $semester->id)->count();
            if ($existingJurnal > 0) {
                $this->warn("  Sudah pernah di-lock ({$existingJurnal} jurnal snapshots). Lewati.");
                continue;
            }

            // Ambil semua siswa yang pernah ada di semester ini
            $semesterSiswaRecords = SemesterSiswa::where('semester_id', $semester->id)
                ->with('siswa')
                ->get();

            // Fallback: jika semester_siswa kosong, isi dari data siswa aktif
            if ($semesterSiswaRecords->isEmpty()) {
                $this->warn("  semester_siswa kosong. Mengisi dari data siswa aktif...");
                $aktifSiswas = Siswa::where('status', 'aktif')->get();
                foreach ($aktifSiswas as $siswa) {
                    SemesterSiswa::firstOrCreate(
                        ['semester_id' => $semester->id, 'siswa_id' => $siswa->id],
                        ['kelas_id' => $siswa->kelas_tartil_id, 'kelas_reguler_id' => $siswa->kelas_reguler_id]
                    );
                }
                $semesterSiswaRecords = SemesterSiswa::where('semester_id', $semester->id)
                    ->with('siswa')
                    ->get();
            }

            $this->info("  Siswa ditemukan: {$semesterSiswaRecords->count()}");

            // 1. Snapshot R2
            $r2Count = 0;
            foreach ($semesterSiswaRecords as $ss) {
                $siswa = $ss->siswa;
                if (!$siswa) continue;
                $kelasId = $ss->kelas_id ?? $siswa->kelas_tartil_id;
                if (!$kelasId) continue;
                $kelas = Kelas::find($kelasId);
                if (!$kelas) continue;

                try {
                    RekapR2Akhir::calculateAndSave($siswa, $semester, $kelas);
                    $r2Count++;
                } catch (\Throwable $e) {
                    $this->error("    R2 gagal: {$siswa->nis} — {$e->getMessage()}");
                }
            }
            SemesterAuditLog::log($semester, 'r2', 'retroactive', $r2Count);
            $this->info("  ✅ R2: {$r2Count} siswa di-lock");

            // 2. Snapshot Jurnal
            $jurnalCount = 0;
            foreach ($semesterSiswaRecords as $ss) {
                $siswa = $ss->siswa;
                if (!$siswa) continue;
                $kelasId = $ss->kelas_id ?? $siswa->kelas_tartil_id;
                if (!$kelasId) continue;
                $kelas = Kelas::find($kelasId);
                if (!$kelas) continue;

                try {
                    RekapJurnalSemester::snapshot($siswa, $semester, $kelas);
                    $jurnalCount++;
                } catch (\Throwable $e) {
                    $this->error("    Jurnal gagal: {$siswa->nis} — {$e->getMessage()}");
                }
            }
            SemesterAuditLog::log($semester, 'jurnal', 'retroactive', $jurnalCount);
            $this->info("  ✅ Jurnal: {$jurnalCount} siswa di-lock");

            // 3. Snapshot Munaqosyah
            $mqCount = 0;
            foreach ($semesterSiswaRecords as $ss) {
                $siswa = $ss->siswa;
                if (!$siswa) continue;

                try {
                    RekapMunaqosyahSemester::snapshot($siswa, $semester);
                    $mqCount++;
                } catch (\Throwable $e) {
                    $this->error("    Munaqosyah gagal: {$siswa->nis}");
                }
            }
            SemesterAuditLog::log($semester, 'munaqosyah', 'retroactive', $mqCount);
            $this->info("  ✅ Munaqosyah: {$mqCount} siswa di-lock");

            // 4. Snapshot Riwayat
            $rwCount = 0;
            foreach ($semesterSiswaRecords as $ss) {
                $siswa = $ss->siswa;
                if (!$siswa) continue;

                try {
                    RekapRiwayatSemester::snapshot($siswa, $semester);
                    $rwCount++;
                } catch (\Throwable $e) {
                    $this->error("    Riwayat gagal: {$siswa->nis}");
                }
            }
            SemesterAuditLog::log($semester, 'riwayat', 'retroactive', $rwCount);
            $this->info("  ✅ Riwayat: {$rwCount} siswa di-lock");

            // 5. Snapshot Kop Surat (jika belum ada)
            $existingKop = KopSuratRapor::where('semester_id', $semester->id)->first();
            if (!$existingKop) {
                try {
                    KopSuratRapor::snapshotSemester($semester->id);
                    SemesterAuditLog::log($semester, 'kop_surat', 'retroactive', 1);
                    $this->info("  ✅ Kop surat di-arsip");
                } catch (\Throwable $e) {
                    $this->error("  ❌ Kop surat gagal: {$e->getMessage()}");
                }
            } else {
                $this->info("  ⏭️ Kop surat sudah ada, lewati");
            }

            $this->info("  ──────────────────────────────────────");
            $this->info("  SELESAI: {$semester->nama}");
        }

        $this->info("\n✅ Semua semester berhasil di-proses.");
        return 0;
    }
}
