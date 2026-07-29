<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Semester;
use App\Models\SemesterKelas;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Seeder penempatan kelas tartil/tahfidz dari file import.xlsx.
 *
 * File Excel diharapkan memiliki struktur:
 *   - Baris 1-4: header/judul (diabaikan)
 *   - Kolom A: nama program (contoh: BILQOLAM 1) — muncul di baris pembuka setiap kelompok
 *   - Kolom B: nomor urut
 *   - Kolom D: No Induk (NIS)
 *   - Kolom E: Nama siswa
 *   - Kolom F: Kelas Reguler (hanya informasi, tidak dipakai untuk penempatan)
 *
 * Mapping program ke kelas tartil dapat disesuaikan di array $programMapping.
 */
class PenempatanKelasTartilSeeder extends Seeder
{
    /**
     * Mapping nama program di Excel → nama kelas tartil di database.
     * Sesuaikan jika nama program di Excel berbeda.
     */
    private array $programMapping = [
        'BILQOLAM 1' => 'BQ 1',
        'BILQOLAM 2' => 'BQ 2',
        'BILQOLAM 3' => 'BQ 3',
        'BILQOLAM 4' => 'BQ 4',
        'JUZ AMMA' => 'Tahfidz',
        'MARHALAH 1' => 'Tartil',
        'MARHALAH 2' => 'Tartil',
        'MARHALAH 3' => 'Tartil',
        'MUNAQOSYAH' => 'Tartil',
        'TAHFIDZ' => 'Tahfidz',
    ];

    /**
     * Jika true, siswa yang sudah punya kelas_tartil_id akan ditimpa.
     * Jika false, hanya siswa yang kelas_tartil_id-nya null yang diproses.
     */
    private bool $overwrite = false;

    /**
     * Path relatif ke file Excel penempatan.
     */
    private string $filePath = 'import.xlsx';

    public function run(): void
    {
        $path = base_path($this->filePath);

        if (! file_exists($path)) {
            $this->command->error("File tidak ditemukan: {$path}");

            return;
        }

        // Muat semua kelas tartil aktif ke memory
        $kelasTartil = Kelas::where('status', 'aktif')
            ->get()
            ->keyBy(fn ($k) => strtoupper(trim($k->nama)));

        // Validasi mapping
        $mappingKelasId = [];
        foreach ($this->programMapping as $program => $kelasNama) {
            $key = strtoupper(trim($kelasNama));
            if (! $kelasTartil->has($key)) {
                $this->command->error("Kelas tartil '{$kelasNama}' tidak ditemukan di database. Periksa mapping.");

                return;
            }
            $mappingKelasId[strtoupper(trim($program))] = $kelasTartil->get($key)->id;
        }

        // Ambil semester aktif untuk update semester_siswa & semester_kelas
        $semesterAktif = Semester::aktif()->first();
        if (! $semesterAktif) {
            $this->command->warn('Tidak ada semester aktif. Penempatan kelas tetap diupdate, tapi data semester tidak disinkronkan.');
        }

        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $currentProgram = null;
        $currentKelasId = null;
        $sukses = 0;
        $gagal = 0;
        $tidakDitemukan = 0;
        $skip = 0;
        $detailGagal = [];
        $now = now();

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $line = $index + 1;

                // Abaikan baris awal (header/judul)
                if ($line < 5) {
                    continue;
                }

                $programCell = trim((string) ($row[0] ?? ''));
                $noUrut = trim((string) ($row[1] ?? ''));
                $nis = trim((string) ($row[3] ?? ''));
                $nama = trim((string) ($row[4] ?? ''));
                $kelasReguler = trim((string) ($row[5] ?? ''));

                // Deteksi program baru dari kolom A
                if ($programCell !== '') {
                    $currentProgram = strtoupper(trim($programCell));
                    $currentKelasId = $mappingKelasId[$currentProgram] ?? null;

                    if (! $currentKelasId) {
                        $this->command->warn("Baris {$line}: Program '{$programCell}' tidak ada di mapping. Baris-baris berikutnya diabaikan sampai program berikutnya.");
                    } else {
                        $this->command->info("Memproses {$programCell} → {$this->programMapping[$currentProgram]}");
                    }

                    continue;
                }

                // Lewati baris kosong atau tanpa NIS
                if ($nis === '' || $nama === '') {
                    continue;
                }

                if (! $currentKelasId) {
                    $skip++;

                    continue;
                }

                // Cari siswa berdasarkan NIS
                $siswa = Siswa::where('nis', $nis)->first();

                if (! $siswa) {
                    $tidakDitemukan++;
                    $detailGagal[] = "Baris {$line}: NIS {$nis} ({$nama}) tidak ditemukan di database.";

                    continue;
                }

                // Jangan timpa jika overwrite = false
                if (! $this->overwrite && $siswa->kelas_tartil_id !== null) {
                    $skip++;

                    continue;
                }

                // Update penempatan kelas tartil
                $siswa->update([
                    'kelas_tartil_id' => $currentKelasId,
                    'tanggal_masuk_kelas_tartil' => $siswa->tanggal_masuk_kelas_tartil ?? $semesterAktif?->tanggal_mulai ?? $now,
                    'keterangan_status' => 'Ditempatkan ke '.Kelas::find($currentKelasId)->nama.' via seeder',
                ]);

                // Sinkronkan ke semester aktif
                if ($semesterAktif) {
                    SemesterSiswa::updateOrCreate(
                        [
                            'semester_id' => $semesterAktif->id,
                            'siswa_id' => $siswa->id,
                        ],
                        [
                            'kelas_id' => $currentKelasId,
                            'kelas_reguler_id' => $siswa->kelas_reguler_id,
                            'status_siswa' => 'aktif',
                            'keterangan' => 'Penempatan kelas tartil dari import.xlsx',
                        ]
                    );

                    SemesterKelas::firstOrCreate(
                        [
                            'semester_id' => $semesterAktif->id,
                            'kelas_id' => $currentKelasId,
                        ],
                        [
                            'jumlah_siswa' => 0,
                            'keterangan' => 'Kelas aktif',
                        ]
                    );
                }

                $sukses++;
            }

            // Hitung ulang jumlah siswa per kelas di semester_kelas jika semester aktif ada
            if ($semesterAktif) {
                foreach ($mappingKelasId as $kelasId) {
                    $jumlah = Siswa::where('kelas_tartil_id', $kelasId)
                        ->where('status', 'aktif')
                        ->count();

                    SemesterKelas::where('semester_id', $semesterAktif->id)
                        ->where('kelas_id', $kelasId)
                        ->update(['jumlah_siswa' => $jumlah, 'updated_at' => $now]);
                }
            }

            DB::commit();

            Log::info('Penempatan kelas tartil via seeder', [
                'sukses' => $sukses,
                'gagal' => $gagal,
                'tidak_ditemukan' => $tidakDitemukan,
                'skip' => $skip,
                'semester_aktif' => $semesterAktif?->id,
            ]);

            $this->command->info('Penempatan selesai.');
            $this->command->info("  Sukses: {$sukses}");
            $this->command->info("  NIS tidak ditemukan: {$tidakDitemukan}");
            $this->command->info("  Dilewati (sudah punya kelas / program tidak dikenali): {$skip}");

            if (! empty($detailGagal)) {
                $this->command->warn('Detail NIS tidak ditemukan (20 pertama):');
                foreach (array_slice($detailGagal, 0, 20) as $err) {
                    $this->command->warn('  - '.$err);
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal penempatan kelas tartil via seeder', ['error' => $e->getMessage()]);
            $this->command->error('Gagal: '.$e->getMessage());
            throw $e;
        }
    }
}
