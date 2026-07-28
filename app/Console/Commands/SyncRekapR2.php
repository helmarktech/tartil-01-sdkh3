<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Semester;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\RekapR2Akhir;

class SyncRekapR2 extends Command
{
    protected $signature = 'r2:sync-semester {semester_id?}';
    protected $description = 'Hitung dan simpan rekap R2 untuk semester yang sudah ditutup (data lama)';

    public function handle()
    {
        $semesterId = $this->argument('semester_id');

        if ($semesterId) {
            $semesters = Semester::where('id', $semesterId)->where('status', 'ditutup')->get();
        } else {
            $semesters = Semester::where('status', 'ditutup')->get();
        }

        if ($semesters->isEmpty()) {
            $this->error('Tidak ada semester yang sudah ditutup.');
            return 1;
        }

        foreach ($semesters as $semester) {
            $this->info("Memproses semester: {$semester->nama} ({$semester->tahun_ajaran})");

            $kelasList = Kelas::where('status', 'aktif')->get();
            $totalSiswa = 0;

            foreach ($kelasList as $kelas) {
                $siswas = Siswa::where('kelas_tartil_id', $kelas->id)
                    ->where('status', 'aktif')
                    ->get();

                foreach ($siswas as $siswa) {
                    $exists = RekapR2Akhir::where('semester_id', $semester->id)
                        ->where('siswa_id', $siswa->id)
                        ->exists();

                    if (!$exists) {
                        try {
                            RekapR2Akhir::calculateAndSave($siswa, $semester, $kelas);
                            $totalSiswa++;
                            $this->info("  ✓ {$siswa->nama} — R2 Akhir locked");
                        } catch (\Throwable $e) {
                            $this->warn("  ✗ {$siswa->nama} — gagal: " . $e->getMessage());
                        }
                    }
                }
            }

            $this->info("Semester {$semester->nama}: {$totalSiswa} siswa di-lock.\n");
        }

        $this->info('Sync rekap R2 selesai.');
        return 0;
    }
}
