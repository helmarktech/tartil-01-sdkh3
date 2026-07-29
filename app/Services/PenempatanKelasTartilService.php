<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Semester;
use App\Models\SemesterKelas;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Output\OutputInterface;

class PenempatanKelasTartilService
{
    /**
     * Mapping nama program di Excel → nama kelas tartil di database.
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

    public static function process(string $path, OutputInterface $output, bool $overwrite = false): array
    {
        if (! file_exists($path)) {
            throw new \InvalidArgumentException("File tidak ditemukan: {$path}");
        }

        $instance = new self;

        $kelasTartil = Kelas::where('status', 'aktif')
            ->get()
            ->keyBy(fn ($k) => strtoupper(trim($k->nama)));

        $mappingKelasId = [];
        foreach ($instance->programMapping as $program => $kelasNama) {
            $key = strtoupper(trim($kelasNama));
            if (! $kelasTartil->has($key)) {
                throw new \InvalidArgumentException("Kelas tartil '{$kelasNama}' tidak ditemukan di database. Periksa mapping.");
            }
            $mappingKelasId[strtoupper(trim($program))] = $kelasTartil->get($key)->id;
        }

        $semesterAktif = Semester::aktif()->first();
        if (! $semesterAktif) {
            $output->writeln('<comment>Tidak ada semester aktif. Penempatan kelas tetap diupdate, tapi data semester tidak disinkronkan.</comment>');
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

                if ($line < 5) {
                    continue;
                }

                $programCell = trim((string) ($row[0] ?? ''));
                $nis = trim((string) ($row[3] ?? ''));
                $nama = trim((string) ($row[4] ?? ''));

                if ($programCell !== '') {
                    $currentProgram = strtoupper(trim($programCell));
                    $currentKelasId = $mappingKelasId[$currentProgram] ?? null;

                    if (! $currentKelasId) {
                        $output->writeln("<comment>Baris {$line}: Program '{$programCell}' tidak ada di mapping. Baris-baris berikutnya diabaikan sampai program berikutnya.</comment>");
                    } else {
                        $output->writeln("<info>Memproses {$programCell} → {$instance->programMapping[$currentProgram]}</info>");
                    }

                    continue;
                }

                if ($nis === '' || $nama === '') {
                    continue;
                }

                if (! $currentKelasId) {
                    $skip++;

                    continue;
                }

                $siswa = Siswa::where('nis', $nis)->first();

                if (! $siswa) {
                    $tidakDitemukan++;
                    $detailGagal[] = "Baris {$line}: NIS {$nis} ({$nama}) tidak ditemukan di database.";

                    continue;
                }

                if (! $overwrite && $siswa->kelas_tartil_id !== null) {
                    $skip++;

                    continue;
                }

                $siswa->update([
                    'kelas_tartil_id' => $currentKelasId,
                    'tanggal_masuk_kelas_tartil' => $siswa->tanggal_masuk_kelas_tartil ?? $semesterAktif?->tanggal_mulai ?? $now,
                    'keterangan_status' => 'Ditempatkan ke '.Kelas::find($currentKelasId)->nama.' via command',
                ]);

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

            Log::info('Penempatan kelas tartil', [
                'sukses' => $sukses,
                'gagal' => $gagal,
                'tidak_ditemukan' => $tidakDitemukan,
                'skip' => $skip,
                'semester_aktif' => $semesterAktif?->id,
                'file' => $path,
            ]);

            return [
                'sukses' => $sukses,
                'gagal' => $gagal,
                'tidak_ditemukan' => $tidakDitemukan,
                'skip' => $skip,
                'detailGagal' => $detailGagal,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal penempatan kelas tartil', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
