<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PenilaianRaporInternal;
use App\Models\PenilaianRaporNilai;
use App\Models\IndikatorPenilaian;
use App\Models\Siswa;
use App\Models\Semester;
use App\Models\Guru;

class PenilaianRaporInternalSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::aktif()->first();
        if (!$semester) {
            $this->command->warn('Tidak ada semester aktif. Skip seeder penilaian.');
            return;
        }

        // Hanya 1 penilaian per semester
        $penilaian = PenilaianRaporInternal::create([
            'nama' => 'Penilaian Rapor ' . $semester->nama,
            'semester_id' => $semester->id,
            'status' => 'aktif',
        ]);

        $this->command->info("Created: {$penilaian->nama}");

        $gurus = Guru::whereHas('kelas', fn($q) => $q->where('status', 'aktif'))->get();
        $totalNilai = 0;

        foreach ($gurus as $guru) {
            $kelasList = $guru->kelas()->where('status', 'aktif')->get();

            foreach ($kelasList as $kelas) {
                $siswas = Siswa::where('kelas_tartil_id', $kelas->id)
                    ->where('status', 'aktif')
                    ->get();

                if ($siswas->isEmpty()) continue;

                $indikators = IndikatorPenilaian::byJenis($kelas->jenis);
                if ($indikators->isEmpty()) continue;

                $batch = [];
                $now = now();

                foreach ($siswas as $siswa) {
                    foreach ($indikators as $ind) {
                        $batch[] = [
                            'penilaian_id' => $penilaian->id,
                            'siswa_id' => $siswa->id,
                            'indikator_penilaian_id' => $ind->id,
                            'nilai' => rand(75, 98),
                            'diisi_oleh' => $guru->id,
                            'tanggal_diisi' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                foreach (array_chunk($batch, 1000) as $chunk) {
                    PenilaianRaporNilai::insert($chunk);
                }

                $totalNilai += count($batch);
                $this->command->info("  {$kelas->nama}: " . count($batch) . " nilai untuk {$siswas->count()} siswa");
            }
        }

        $this->command->info("Total: {$totalNilai} nilai disimpan.");
    }
}
