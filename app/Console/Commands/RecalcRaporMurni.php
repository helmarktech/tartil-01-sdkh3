<?php

namespace App\Console\Commands;

use App\Models\RekapR2Akhir;
use Illuminate\Console\Command;

class RecalcRaporMurni extends Command
{
    protected $signature = 'r2:recalc-rapor-murni {--semester_id=} {--force}';

    protected $description = 'Hitung ulang nilai rapor (r2_akhir = r2_penilaian) pada snapshot semester yang sudah ditutup, mengikuti aturan baru: nilai rapor murni dari Penilaian Rapor Internal';

    public function handle()
    {
        $semesterId = $this->option('semester_id');

        $query = RekapR2Akhir::whereHas('semester', fn ($q) => $q->where('status', 'ditutup'));
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        $total = (clone $query)->count();
        if ($total === 0) {
            $this->error('Tidak ada snapshot R2 pada semester yang sudah ditutup.');

            return 1;
        }

        $this->warn("Akan menghitung ulang {$total} snapshot: r2_akhir = r2_penilaian (nilai B/C/K harian tidak lagi masuk rapor).");
        $this->warn('Catatan: siswa tanpa nilai penilaian internal (jumlah_terisi = 0) akan mendapat nilai rapor 0.');
        if (! $this->option('force') && ! $this->confirm('Lanjutkan?')) {
            $this->info('Dibatalkan.');

            return 0;
        }

        $updated = 0;
        $unchanged = 0;
        $tanpaPenilaian = 0;

        $query->orderBy('id')->chunk(200, function ($rows) use (&$updated, &$unchanged, &$tanpaPenilaian) {
            foreach ($rows as $rekap) {
                if ($rekap->jumlah_terisi == 0) {
                    $tanpaPenilaian++;
                }

                if ((int) $rekap->r2_akhir === (int) $rekap->r2_penilaian) {
                    $unchanged++;

                    continue;
                }

                $rekap->r2_akhir = $rekap->r2_penilaian;
                $rekap->last_calculated = now();
                $rekap->save();
                $updated++;
            }
        });

        $this->info("Selesai. Diupdate: {$updated}, sudah sesuai: {$unchanged}.");
        if ($tanpaPenilaian > 0) {
            $this->warn("Perhatian: {$tanpaPenilaian} snapshot tidak punya nilai penilaian internal (jumlah_terisi = 0) — nilai rapornya 0.");
        }

        return 0;
    }
}
