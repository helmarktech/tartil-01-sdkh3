<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RekapR2Akhir;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\PenilaianRaporInternal;

class PrecalculateR2 extends Command
{
    protected $signature = 'r2:precalculate {--semester_id=} {--kelas_id=}';
    protected $description = 'Pre-calculate dan cache R2 untuk semua siswa atau kelas tertentu';

    public function handle(): void
    {
        $semesterId = $this->option('semester_id');
        $kelasId = $this->option('kelas_id');

        $penilaian = PenilaianRaporInternal::where('status', 'aktif')->first();
        if (!$penilaian) {
            $this->error('Tidak ada penilaian aktif.');
            return;
        }

        $semester = $semesterId
            ? Semester::find($semesterId)
            : Semester::aktif()->first();

        if (!$semester) {
            $this->error('Semester tidak ditemukan.');
            return;
        }

        $kelasQuery = Kelas::where('status', 'aktif');
        if ($kelasId) $kelasQuery->where('id', $kelasId);
        $kelasList = $kelasQuery->get();

        $total = 0;
        $bar = $this->output->createProgressBar($kelasList->count());

        foreach ($kelasList as $kelas) {
            $siswas = Siswa::where('kelas_tartil_id', $kelas->id)
                ->where('status', 'aktif')
                ->cursor(); // Memory efficient untuk ribuan siswa

            foreach ($siswas as $siswa) {
                RekapR2Akhir::calculateAndSave($siswa, $semester, $kelas);
                $total++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // Cleanup: Hapus R2 cache milik siswa non-aktif
        $deleted = RekapR2Akhir::whereHas('siswa', fn($q) => $q->where('status', '!=', 'aktif'))->delete();
        if ($deleted > 0) {
            $this->warn("{$deleted} R2 cache siswa non-aktif dihapus.");
        }

        $this->info("R2 dihitung untuk {$total} siswa di {$kelasList->count()} kelas.");
    }
}
