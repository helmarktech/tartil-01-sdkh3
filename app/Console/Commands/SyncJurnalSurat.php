<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\JurnalKelas;
use App\Models\JurnalHarian;

class SyncJurnalSurat extends Command
{
    protected $signature = 'jurnal:sync-surat';
    protected $description = 'Sync surat_id, ayat, halaman, materi, topik, rencana dari jurnal_kelas ke jurnal_harians';

    public function handle()
    {
        $jurnalKelasList = JurnalKelas::whereNotNull('surat_id')
            ->orWhereNotNull('ayat')
            ->orWhereNotNull('halaman_juz')
            ->orWhereNotNull('materi_pembelajaran')
            ->orWhereNotNull('topik')
            ->orWhereNotNull('rencana')
            ->get();

        $synced = 0;
        foreach ($jurnalKelasList as $jk) {
            // Parse ayat
            $mulai = null;
            $selesai = null;
            if ($jk->ayat) {
                if (str_contains($jk->ayat, '-')) {
                    [$mulai, $selesai] = array_map('trim', explode('-', $jk->ayat, 2));
                    $mulai = is_numeric($mulai) ? (int) $mulai : null;
                    $selesai = is_numeric($selesai) ? (int) $selesai : null;
                } else {
                    $mulai = is_numeric(trim($jk->ayat)) ? (int) trim($jk->ayat) : null;
                }
            }

            $updated = JurnalHarian::where('kelas_id', $jk->kelas_id)
                ->where('tanggal', $jk->tanggal)
                ->update([
                    'surat_id' => $jk->surat_id,
                    'ayat_mulai' => $mulai,
                    'ayat_selesai' => $selesai,
                    'halaman' => $jk->halaman_juz,
                    'materi' => $jk->materi_pembelajaran,
                    'topik' => $jk->topik,
                    'rencana' => $jk->rencana,
                ]);

            $synced += $updated;
        }

        $this->info("Sync selesai. {$synced} record jurnal_harians diperbarui.");
        return 0;
    }
}
