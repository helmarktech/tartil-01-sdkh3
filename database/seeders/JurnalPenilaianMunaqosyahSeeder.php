<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\JurnalHarian;
use App\Models\PenilaianRaporInternal;
use App\Models\PenilaianRaporNilai;
use App\Models\IndikatorPenilaian;
use App\Models\UjianMunaqosyah;
use App\Models\MunaqosyahPendaftaran;
use App\Models\Surat;
use App\Models\RekapR2Akhir;
use App\Models\RekapJurnalSemester;
use App\Models\RekapMunaqosyahSemester;
use App\Models\RekapRiwayatSemester;
use App\Models\SemesterAuditLog;
use Carbon\Carbon;

class JurnalPenilaianMunaqosyahSeeder extends Seeder
{
    public function run(): void
    {
        $semGanjil = Semester::where('jenis', 'ganjil')->where('tahun_ajaran', '2025/2026')->first();
        $semGenap = Semester::where('jenis', 'genap')->where('tahun_ajaran', '2025/2026')->first();
        $siswas = Siswa::where('status', 'aktif')->get();
        $kelasList = Kelas::where('status', 'aktif')->get();
        $guruIdDefault = $kelasList->first()?->guru_id;

        // Ambil 30 surat pertama untuk variasi
        $surats = Surat::orderBy('id')->take(30)->get();
        $catatanPool = [
            'Membaca dengan tartid', 'Perlu latihan mad', 'Baik, lanjutkan',
            'Makhraj belum tepat', 'Lancar', 'Perlu perbaikan tajwid',
            'Bagus', 'Kurang fokus', 'Meningkat', 'Perbanyak latihan',
            'Alhamdulillah lancar', 'Mengulang ayat', 'Sudah hafal',
            'Perlu pendampingan', 'Mudah-mudahan istiqomah',
        ];

        // ════════════════════════════════════════════
        // JURNAL HARIAN SEMESTER GANJIL (Juli - Des 2025)
        // ════════════════════════════════════════════
        if ($semGanjil) {
            $start = Carbon::parse('2025-07-01');
            $end = Carbon::parse('2025-12-20');
            $jurnalCount = 0;

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                // Skip weekend
                if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) continue;

                foreach ($siswas as $siswa) {
                    $kelasId = $siswa->kelas_tartil_id;
                    if (!$kelasId) continue;

                    $penilaianOptions = ['B', 'B', 'B', 'B', 'C', 'C', 'K'];
                    $penilaian = $penilaianOptions[array_rand($penilaianOptions)];
                    $surat = $surats->random();
                    $ayatMulai = rand(1, 50);
                    $ayatSelesai = min($ayatMulai + rand(2, 8), $surat->jumlah_ayat ?? 100);

                    $existing = JurnalHarian::where([
                        'siswa_id' => $siswa->id,
                        'kelas_id' => $kelasId,
                        'tanggal' => $date->toDateString(),
                    ])->first();

                    if (!$existing) {
                        JurnalHarian::create([
                            'semester_id' => $semGanjil->id,
                            'kelas_id' => $kelasId,
                            'guru_id' => $kelasList->firstWhere('id', $kelasId)?->guru_id ?? $guruIdDefault,
                            'siswa_id' => $siswa->id,
                            'tanggal' => $date->toDateString(),
                            'penilaian' => $penilaian,
                            'surat_id' => $surat->id,
                            'ayat_mulai' => $ayatMulai,
                            'ayat_selesai' => $ayatSelesai,
                            'halaman' => 'Juz ' . rand(1, 3) . ' hal ' . rand(10, 50),
                            'materi' => ['Tajwid', 'Makhraj', 'Mad', 'Ghunnah', 'Qalqalah'][rand(0, 4)],
                            'topik' => ['Pengenalan huruf', 'Latihan baca', 'Hafalan', 'Evaluasi'][rand(0, 3)],
                            'rencana' => ['Lanjut ayat berikut', 'Mengulang', 'Latihan mandiri', 'Tes'][rand(0, 3)],
                            'catatan' => $catatanPool[array_rand($catatanPool)],
                        ]);
                        $jurnalCount++;
                    }
                }
            }
            $this->command->info("Jurnal Ganjil: {$jurnalCount} entri — created.");
        }

        // ════════════════════════════════════════════
        // JURNAL HARIAN SEMESTER GENAP (Jan - Jun 2026)
        // ════════════════════════════════════════════
        if ($semGenap) {
            $start = Carbon::parse('2026-01-05');
            $end = Carbon::parse('2026-06-20');
            $jurnalCount = 0;

            for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                if (in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) continue;

                foreach ($siswas as $siswa) {
                    $kelasId = $siswa->kelas_tartil_id;
                    if (!$kelasId) continue;

                    $penilaianOptions = ['B', 'B', 'B', 'B', 'C', 'C', 'K'];
                    $penilaian = $penilaianOptions[array_rand($penilaianOptions)];
                    $surat = $surats->random();
                    $ayatMulai = rand(1, 50);
                    $ayatSelesai = min($ayatMulai + rand(2, 8), $surat->jumlah_ayat ?? 100);

                    $existing = JurnalHarian::where([
                        'siswa_id' => $siswa->id,
                        'kelas_id' => $kelasId,
                        'tanggal' => $date->toDateString(),
                    ])->first();

                    if (!$existing) {
                        JurnalHarian::create([
                            'semester_id' => $semGenap->id,
                            'kelas_id' => $kelasId,
                            'guru_id' => $kelasList->firstWhere('id', $kelasId)?->guru_id ?? $guruIdDefault,
                            'siswa_id' => $siswa->id,
                            'tanggal' => $date->toDateString(),
                            'penilaian' => $penilaian,
                            'surat_id' => $surat->id,
                            'ayat_mulai' => $ayatMulai,
                            'ayat_selesai' => $ayatSelesai,
                            'halaman' => 'Juz ' . rand(1, 3) . ' hal ' . rand(10, 50),
                            'materi' => ['Tajwid', 'Makhraj', 'Mad', 'Ghunnah', 'Qalqalah'][rand(0, 4)],
                            'topik' => ['Pengenalan huruf', 'Latihan baca', 'Hafalan', 'Evaluasi'][rand(0, 3)],
                            'rencana' => ['Lanjut ayat berikut', 'Mengulang', 'Latihan mandiri', 'Tes'][rand(0, 3)],
                            'catatan' => $catatanPool[array_rand($catatanPool)],
                        ]);
                        $jurnalCount++;
                    }
                }
            }
            $this->command->info("Jurnal Genap: {$jurnalCount} entri — created.");
        }

        // ════════════════════════════════════════════
        // INDikATOR PENILAIAN (per jenis kelas)
        // ════════════════════════════════════════════
        $jenisKelasList = ['BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz'];
        $indikatorNames = ['Tajwid', 'Makhraj', 'Mad & Ghunnah', 'Qiraah', 'Kelancaran'];

        foreach ($jenisKelasList as $jenisKelas) {
            $existing = IndikatorPenilaian::where('jenis_kelas', $jenisKelas)->count();
            if ($existing == 0) {
                foreach ($indikatorNames as $i => $nama) {
                    IndikatorPenilaian::create([
                        'nama_indikator' => $nama,
                        'jenis_kelas' => $jenisKelas,
                        'urutan' => $i + 1,
                        'is_default' => true,
                    ]);
                }
            }
        }

        // ════════════════════════════════════════════
        // PENILAIAN RAPOR SEMESTER GANJIL
        // ════════════════════════════════════════════
        if ($semGanjil) {
            $penilaianGanjil = PenilaianRaporInternal::firstOrCreate(
                ['nama' => 'Penilaian Ganjil 2025/2026', 'semester_id' => $semGanjil->id],
                ['status' => 'selesai']
            );

            $nilaiCount = 0;
            foreach ($siswas as $siswa) {
                $kelas = $kelasList->firstWhere('id', $siswa->kelas_tartil_id);
                $jenisKelas = $kelas ? $kelas->jenis : 'BQ 1';
                $indikators = IndikatorPenilaian::byJenis($jenisKelas);

                foreach ($indikators as $indikator) {
                    PenilaianRaporNilai::firstOrCreate(
                        [
                            'penilaian_id' => $penilaianGanjil->id,
                            'siswa_id' => $siswa->id,
                            'indikator_penilaian_id' => $indikator->id,
                        ],
                        [
                            'nilai' => rand(65, 95),
                            'diisi_oleh' => $guruIdDefault,
                            'tanggal_diisi' => '2025-12-15',
                        ]
                    );
                    $nilaiCount++;
                }
            }
            $this->command->info("Penilaian Ganjil: {$nilaiCount} nilai — created.");
        }

        // ════════════════════════════════════════════
        // PENILAIAN RAPOR SEMESTER GENAP
        // ════════════════════════════════════════════
        if ($semGenap) {
            $penilaianGenap = PenilaianRaporInternal::firstOrCreate(
                ['nama' => 'Penilaian Genap 2025/2026', 'semester_id' => $semGenap->id],
                ['status' => 'aktif']
            );

            $nilaiCount = 0;
            foreach ($siswas as $siswa) {
                $kelas = $kelasList->firstWhere('id', $siswa->kelas_tartil_id);
                $jenisKelas = $kelas ? $kelas->jenis : 'BQ 1';
                $indikators = IndikatorPenilaian::byJenis($jenisKelas);

                foreach ($indikators as $indikator) {
                    PenilaianRaporNilai::firstOrCreate(
                        [
                            'penilaian_id' => $penilaianGenap->id,
                            'siswa_id' => $siswa->id,
                            'indikator_penilaian_id' => $indikator->id,
                        ],
                        [
                            'nilai' => rand(65, 95),
                            'diisi_oleh' => $guruIdDefault,
                            'tanggal_diisi' => '2026-03-15',
                        ]
                    );
                    $nilaiCount++;
                }
            }
            $this->command->info("Penilaian Genap: {$nilaiCount} nilai — created.");
        }

        // ════════════════════════════════════════════
        // MUNAQOSYAH 3 TINGKAT (unit, yayasan, pesantren)
        // ════════════════════════════════════════════
        $tingkatData = [
            ['nama' => 'Munaqosyah Unit', 'tingkat' => 'unit'],
            ['nama' => 'Munaqosyah Yayasan', 'tingkat' => 'yayasan'],
            ['nama' => 'Munaqosyah Pesantren', 'tingkat' => 'pesantren'],
        ];

        foreach ($tingkatData as $i => $td) {
            $ujian = UjianMunaqosyah::firstOrCreate(
                ['nama' => $td['nama'], 'semester_id' => $semGanjil->id ?? 1],
                [
                    'tanggal_ujian' => Carbon::parse('2025-12-0' . ($i + 1)),
                    'tingkat' => $td['tingkat'],
                    'status' => 'selesai',
                ]
            );

            // Daftarkan 20 siswa random
            foreach ($siswas->random(min(20, $siswas->count())) as $siswa) {
                $statuses = [
                    MunaqosyahPendaftaran::STATUS_LULUS,
                    MunaqosyahPendaftaran::STATUS_LULUS,
                    MunaqosyahPendaftaran::STATUS_LULUS,
                    MunaqosyahPendaftaran::STATUS_LULUS,
                    MunaqosyahPendaftaran::STATUS_LULUS,
                    MunaqosyahPendaftaran::STATUS_TIDAK_LULUS,
                    MunaqosyahPendaftaran::STATUS_TIDAK_LULUS,
                    MunaqosyahPendaftaran::STATUS_TERDAFTAR,
                ];

                MunaqosyahPendaftaran::firstOrCreate(
                    [
                        'munaqosyah_id' => $ujian->id,
                        'siswa_id' => $siswa->id,
                    ],
                    [
                        'status' => $statuses[array_rand($statuses)],
                        'nilai' => rand(60, 95),
                        'catatan' => ['Alhamdulillah lulus', 'Perlu perbaikan', 'Bagus', 'Lanjutkan'][rand(0, 3)],
                    ]
                );
            }
        }
        $this->command->info("Munaqosyah: 3 tingkat (unit, yayasan, pesantren) — created.");

        // ════════════════════════════════════════════
        // REKAP R2 AKHIR — LOCK DATA SEMESTER GANJIL
        // ════════════════════════════════════════════
        if ($semGanjil) {
            $rekapCount = 0;
            foreach ($siswas as $siswa) {
                $kelasId = $siswa->kelas_tartil_id;
                if (!$kelasId) continue;
                $kelas = $kelasList->firstWhere('id', $kelasId);
                if (!$kelas) continue;

                try {
                    RekapR2Akhir::calculateAndSave($siswa, $semGanjil, $kelas);
                    $rekapCount++;
                } catch (\Throwable $e) {
                    // skip
                }
            }
            SemesterAuditLog::log($semGanjil, 'r2', 'snapshot', $rekapCount, ['source' => 'seeder']);
            $this->command->info("Rekap R2 Ganjil: {$rekapCount} siswa — locked.");
        }

        // ════════════════════════════════════════════
        // SNAPSHOT SEMESTER GANJIL (terkunci)
        // Mengisi rekap_jurnal_semesters, rekap_munaqosyah_semesters,
        // rekap_riwayat_semesters untuk semester yang sudah ditutup.
        // ════════════════════════════════════════════
        if ($semGanjil && $semGanjil->status === 'ditutup') {
            $this->command->info('');
            $this->command->info('Snapshot semester ganjil (terkunci)...');

            // Snapshot Jurnal
            $snapJurnalCount = 0;
            foreach ($siswas as $siswa) {
                $kelasId = $siswa->kelas_tartil_id;
                if (!$kelasId) continue;
                $kelas = $kelasList->firstWhere('id', $kelasId);
                if (!$kelas) continue;

                try {
                    RekapJurnalSemester::snapshot($siswa, $semGanjil, $kelas);
                    $snapJurnalCount++;
                } catch (\Throwable $e) {
                    // skip
                }
            }
            SemesterAuditLog::log($semGanjil, 'jurnal', 'snapshot', $snapJurnalCount, ['source' => 'seeder']);
            $this->command->info("  Jurnal: {$snapJurnalCount} siswa — snapshot.");

            // Snapshot Munaqosyah
            $snapMqCount = 0;
            foreach ($siswas as $siswa) {
                try {
                    RekapMunaqosyahSemester::snapshot($siswa, $semGanjil);
                    $snapMqCount++;
                } catch (\Throwable $e) {
                    // skip
                }
            }
            SemesterAuditLog::log($semGanjil, 'munaqosyah', 'snapshot', $snapMqCount, ['source' => 'seeder']);
            $this->command->info("  Munaqosyah: {$snapMqCount} siswa — snapshot.");

            // Snapshot Riwayat
            $snapRwCount = 0;
            foreach ($siswas as $siswa) {
                try {
                    RekapRiwayatSemester::snapshot($siswa, $semGanjil);
                    $snapRwCount++;
                } catch (\Throwable $e) {
                    // skip
                }
            }
            SemesterAuditLog::log($semGanjil, 'riwayat', 'snapshot', $snapRwCount, ['source' => 'seeder']);
            $this->command->info("  Riwayat: {$snapRwCount} siswa — snapshot.");

            // Snapshot Kop Surat
            try {
                \App\Models\KopSuratRapor::snapshotSemester($semGanjil->id);
                SemesterAuditLog::log($semGanjil, 'kop_surat', 'snapshot', 1, ['source' => 'seeder']);
                $this->command->info("  Kop Surat: di-arsip.");
            } catch (\Throwable $e) {
                $this->command->warn("  Kop Surat: gagal — " . $e->getMessage());
            }
        }
    }
}
