<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JurnalKelas;
use Illuminate\Support\Facades\DB;

class FixPertemuanKeCommand extends Command
{
    protected $signature = 'jurnal:fix-pertemuan-ke';
    protected $description = 'Perbaiki nomor pertemuan_ke agar berurutan berdasarkan tanggal per kelas per bulan';

    public function handle()
    {
        $this->info('Memperbaiki pertemuan_ke...');

        DB::beginTransaction();
        try {
            // Ambil semua jurnal_kelas, urutkan per kelas, tahun, bulan, tanggal
            $jurnals = JurnalKelas::orderBy('kelas_id')
                ->orderByRaw('YEAR(tanggal)')
                ->orderByRaw('MONTH(tanggal)')
                ->orderBy('tanggal')
                ->get();

            $updated = 0;
            $currentKey = null;
            $counter = 0;
            $previousValues = [];

            foreach ($jurnals as $jk) {
                $key = "{$jk->kelas_id}:" . $jk->tanggal->format('Y-m');

                if ($key !== $currentKey) {
                    $currentKey = $key;
                    $counter = 1;
                } else {
                    $counter++;
                }

                if ($jk->pertemuan_ke != $counter) {
                    $previousValues[] = "ID {$jk->id} ({$jk->tanggal->format('d/m/Y')}) : {$jk->pertemuan_ke} → {$counter}";
                    $jk->pertemuan_ke = $counter;
                    $jk->save();
                    $updated++;
                }
            }

            DB::commit();

            $this->info("Perbaikan selesai. {$updated} record diperbarui.");

            if (count($previousValues) > 0) {
                $this->warn('Contoh perubahan:');
                foreach (array_slice($previousValues, 0, 20) as $line) {
                    $this->line($line);
                }
                if (count($previousValues) > 20) {
                    $this->line('... dan ' . (count($previousValues) - 20) . ' perubahan lainnya.');
                }
            }

            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
            return 1;
        }
    }
}
