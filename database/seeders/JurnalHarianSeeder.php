<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JurnalHarian;
use App\Models\RekapJurnalBulanan;
use App\Models\Semester;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Surat;
use App\Models\Guru;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JurnalHarianSeeder extends Seeder
{
    public function run(): void
    {
        $semester = Semester::aktif()->first();
        if (!$semester) {
            $this->command->warn('Tidak ada semester aktif. Skip jurnal seeder.');
            return;
        }

        // Ambil semua surat untuk materi pembelajaran
        $surats = Surat::all();
        if ($surats->isEmpty()) {
            $this->command->warn('Tidak ada data surat. Skip jurnal seeder.');
            return;
        }

        $tanggalMulai = Carbon::parse($semester->tanggal_mulai);
        $tanggalSelesai = Carbon::parse($semester->tanggal_selesai ?? now());

        // Ambil semua kelas aktif dengan guru
        $kelasList = Kelas::where('status', 'aktif')
            ->whereNotNull('guru_id')
            ->get();

        $totalJurnal = 0;

        foreach ($kelasList as $kelas) {
            $siswas = Siswa::where('kelas_tartil_id', $kelas->id)
                ->where('status', 'aktif')
                ->get();

            if ($siswas->isEmpty()) continue;

            // Buat jurnal untuk setiap siswa, setiap hari kerja (Senin-Jumat)
            $tanggal = $tanggalMulai->copy();
            $batch = [];
            $batchSize = 1000;

            while ($tanggal->lte($tanggalSelesai)) {
                // Hanya hari kerja (Senin-Jumat)
                if ($tanggal->isWeekday()) {
                    foreach ($siswas as $siswa) {
                        $surat = $surats->random();
                        $maxAyat = $surat->jumlah_ayat ?? 10;
                        $ayatMulai = rand(1, max(1, $maxAyat - 3));
                        $ayatSelesai = min($ayatMulai + rand(2, 5), $maxAyat);

                        // Nilai: 60% B, 25% C, 15% K
                        $rand = rand(1, 100);
                        $nilai = $rand <= 60 ? 'B' : ($rand <= 85 ? 'C' : 'K');

                        // Catatan random untuk sebagian siswa (40% chance)
                        $catatanList = [
                            'Alhamdulillah lancar',
                            'Perlu latihan lebih',
                            'Sudah mulai mahir',
                            'Masih ragu-ragu di beberapa ayat',
                            'Bagus, tingkatkan terus',
                            'Perlu bimbingan ekstra',
                            'Sangat baik hari ini',
                            'Masih perlu diulang',
                            'Cukup memuaskan',
                            'Belum fokus, perlu motivasi',
                            'Alhamdulillah hafalannya bertambah',
                            'Perlu istirahat sejenak',
                            'Tajwid sudah mulai benar',
                            'Makhraj sudah baik',
                            'Ghunnah masih kurang',
                            'Madd terlalu cepat',
                            'Qalqalah sudah tepat',
                            'Idgham masih perlu latihan',
                            'Ikhfa sudah bagus',
                            'Iqlab perlu diperhatikan',
                            'Lafazh sudah lancar',
                            'Tartil sudah merdu',
                            'Nada masih monoton',
                            'Tempo terlalu lambat',
                            'Waqf sudah benar',
                            'Ibtida masih ragu',
                            'Tafkhim dan tarqiq sudah baik',
                            'Hamzah wasal dan qath belum tepat',
                            'Saktah perlu diperbaiki',
                            'Lam jalalah sudah benar',
                        ];
                        $catatan = rand(1, 100) <= 40 ? $catatanList[array_rand($catatanList)] : null;

                        $batch[] = [
                            'semester_id' => $semester->id,
                            'kelas_id' => $kelas->id,
                            'guru_id' => $kelas->guru_id,
                            'siswa_id' => $siswa->id,
                            'tanggal' => $tanggal->toDateString(),
                            'penilaian' => $nilai,
                            'surat_id' => $surat->id,
                            'ayat_mulai' => $ayatMulai,
                            'ayat_selesai' => $ayatSelesai,
                            'catatan' => $catatan,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        if (count($batch) >= $batchSize) {
                            JurnalHarian::insert($batch);
                            $totalJurnal += count($batch);
                            $batch = [];
                        }
                    }
                }

                $tanggal->addDay();
            }

            // Sisa batch
            if (!empty($batch)) {
                JurnalHarian::insert($batch);
                $totalJurnal += count($batch);
            }

            $this->command->info("  {$kelas->nama}: jurnal untuk {$siswas->count()} siswa selesai");
        }

        $this->command->info("Total jurnal: {$totalJurnal} entries");

        // ── GENERATE REKAP JURNAL BULANAN dari data yang baru diinsert ──
        $this->command->info('Generating rekap jurnal bulanan...');
        $this->generateRekapBulanan($semester);
    }

    /**
     * Hitung agregasi B/C/K per siswa per bulan & simpan ke rekap_jurnal_bulanans.
     * Mirror logic dari JurnalController::updateRekapBulanan()
     */
    private function generateRekapBulanan(Semester $semester): void
    {
        // Ambil semua (kelas_id, bulan) unik dari jurnal yang ada
        $kelasBulanList = JurnalHarian::where('semester_id', $semester->id)
            ->selectRaw('kelas_id, YEAR(tanggal) * 100 + MONTH(tanggal) as bulan')
            ->distinct()
            ->get();

        if ($kelasBulanList->isEmpty()) {
            $this->command->warn('  Tidak ada data jurnal untuk direkap.');
            return;
        }

        $totalRekap = 0;

        foreach ($kelasBulanList as $kb) {
            $aggregasi = JurnalHarian::where('semester_id', $semester->id)
                ->where('kelas_id', $kb->kelas_id)
                ->whereRaw('YEAR(tanggal) * 100 + MONTH(tanggal) = ?', [$kb->bulan])
                ->select(
                    'siswa_id',
                    DB::raw('SUM(CASE WHEN penilaian IS NOT NULL THEN 1 ELSE 0 END) as total_hadir'),
                    DB::raw('SUM(CASE WHEN penilaian = "B" THEN 1 ELSE 0 END) as count_b'),
                    DB::raw('SUM(CASE WHEN penilaian = "C" THEN 1 ELSE 0 END) as count_c'),
                    DB::raw('SUM(CASE WHEN penilaian = "K" THEN 1 ELSE 0 END) as count_k'),
                )
                ->groupBy('siswa_id')
                ->get();

            foreach ($aggregasi as $agg) {
                $totalDinilai = $agg->count_b + $agg->count_c + $agg->count_k;
                $rataRata = null;
                if ($totalDinilai > 0) {
                    $skor = ($agg->count_b * 1.0 + $agg->count_c * 0.67 + $agg->count_k * 0.33) / $totalDinilai;
                    $rataRata = round($skor, 2);
                }

                RekapJurnalBulanan::updateOrCreate(
                    [
                        'semester_id' => $semester->id,
                        'kelas_id' => $kb->kelas_id,
                        'siswa_id' => $agg->siswa_id,
                        'bulan' => (int) $kb->bulan,
                    ],
                    [
                        'total_hadir' => $agg->total_hadir,
                        'count_b' => $agg->count_b,
                        'count_c' => $agg->count_c,
                        'count_k' => $agg->count_k,
                        'rata_rata' => $rataRata,
                    ]
                );
                $totalRekap++;
            }
        }

        $this->command->info("  Rekap: {$totalRekap} rows ({$kelasBulanList->count()} kelas-bulan)");
    }
}
