<?php

namespace App\Services;

use App\Models\KelasReguler;
use App\Models\Semester;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\Console\Output\OutputInterface;

class ImportSiswaService
{
    public static function process(string $path, OutputInterface $output): array
    {
        if (! file_exists($path)) {
            throw new \InvalidArgumentException("File tidak ditemukan: {$path}");
        }

        $semesterAktif = Semester::aktif()->first();
        if (! $semesterAktif) {
            $output->writeln('<comment>Tidak ada semester aktif. Siswa tetap diimport tanpa pendaftaran semester.</comment>');
        }

        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (count($rows) < 2) {
            throw new \InvalidArgumentException('File kosong atau tidak memiliki data.');
        }

        $header = array_map('strtoupper', array_map('trim', $rows[0]));
        $required = ['NIS', 'NAMA', 'JENIS_KELAMIN', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT', 'TANGGAL_LAHIR', 'TEMPAT_LAHIR', 'NAMA_AYAH'];
        $missing = array_diff($required, $header);
        if (! empty($missing)) {
            throw new \InvalidArgumentException('Kolom wajib tidak ditemukan: '.implode(', ', $missing));
        }

        $colIdx = [];
        foreach ($required as $col) {
            $colIdx[$col] = array_search($col, $header);
        }
        $colIdx['NO_HP'] = array_search('NO_HP', $header);
        $colIdx['TANGGAL_MASUK'] = array_search('TANGGAL_MASUK', $header);

        $now = now();
        $sukses = 0;
        $gagal = 0;
        $errors = [];
        $insertData = [];
        $semesterSiswaData = [];

        $kelasRegulers = KelasReguler::all()->keyBy(function ($k) {
            return strtoupper(trim($k->nama)).'|'.(int) $k->jenjang.'|'.strtoupper(trim($k->tingkat));
        });

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) {
                continue;
            }

            $nis = trim((string) ($row[$colIdx['NIS']] ?? ''));
            $nama = trim((string) ($row[$colIdx['NAMA']] ?? ''));
            $jk = trim((string) ($row[$colIdx['JENIS_KELAMIN']] ?? ''));
            $kelasNama = trim((string) ($row[$colIdx['KELAS_NAMA']] ?? ''));
            $kelasJenjang = trim((string) ($row[$colIdx['KELAS_JENJANG']] ?? ''));
            $kelasTingkat = trim((string) ($row[$colIdx['KELAS_TINGKAT']] ?? ''));
            $noHp = $colIdx['NO_HP'] !== false ? trim((string) ($row[$colIdx['NO_HP']] ?? '')) : '';
            if (! $noHp) {
                $noHp = '000000000000';
                $output->writeln('<comment>Baris '.($i + 1).': NO_HP kosong, diisi default 000000000000 untuk NIS '.$nis.'.</comment>');
            }
            $tglMasuk = $colIdx['TANGGAL_MASUK'] !== false ? trim((string) ($row[$colIdx['TANGGAL_MASUK']] ?? '')) : $now->format('Y-m-d');
            $tglLahir = trim((string) ($row[$colIdx['TANGGAL_LAHIR']] ?? ''));
            $tempatLahir = trim((string) ($row[$colIdx['TEMPAT_LAHIR']] ?? ''));
            $namaAyah = trim((string) ($row[$colIdx['NAMA_AYAH']] ?? ''));

            if (! $nis) {
                $errors[] = 'Baris '.($i + 1).': NIS wajib diisi.';
                $gagal++;

                continue;
            }
            if (! $nama) {
                $errors[] = 'Baris '.($i + 1).': Nama wajib diisi.';
                $gagal++;

                continue;
            }
            if (! $jk || ! in_array(strtoupper($jk), ['L', 'P'])) {
                $errors[] = 'Baris '.($i + 1).': Jenis Kelamin harus L atau P.';
                $gagal++;

                continue;
            }
            if (! $kelasNama || ! $kelasJenjang || ! $kelasTingkat) {
                $errors[] = 'Baris '.($i + 1).': Kelas (nama, jenjang, tingkat) wajib diisi.';
                $gagal++;

                continue;
            }
            if (! $tglLahir) {
                $errors[] = 'Baris '.($i + 1).': Tanggal lahir wajib diisi.';
                $gagal++;

                continue;
            }
            $parsedTglLahir = self::parseTanggal($tglLahir);
            if (! $parsedTglLahir) {
                $errors[] = 'Baris '.($i + 1).': Format tanggal lahir tidak valid (gunakan YYYY-MM-DD).';
                $gagal++;

                continue;
            }
            if (! $tempatLahir) {
                $errors[] = 'Baris '.($i + 1).': Tempat lahir wajib diisi.';
                $gagal++;

                continue;
            }
            if (! $namaAyah) {
                $errors[] = 'Baris '.($i + 1).': Nama ayah wajib diisi.';
                $gagal++;

                continue;
            }

            $key = strtoupper($kelasNama).'|'.(int) $kelasJenjang.'|'.strtoupper($kelasTingkat);

            if (! $kelasRegulers->has($key)) {
                $kelasReguler = KelasReguler::create([
                    'nama' => strtoupper($kelasNama),
                    'jenjang' => (int) $kelasJenjang,
                    'tingkat' => strtoupper($kelasTingkat),
                    'is_aktif' => true,
                ]);
                $kelasRegulers->put($key, $kelasReguler);
                $output->writeln("<info>Kelas reguler baru dibuat: {$kelasReguler->nama}</info>");
            }

            $kelasReguler = $kelasRegulers->get($key);

            if (Siswa::where('nis', $nis)->exists()) {
                $errors[] = 'Baris '.($i + 1).': NIS "'.$nis.'" sudah terdaftar.';
                $gagal++;

                continue;
            }

            $insertData[] = [
                'nis' => $nis,
                'nama' => $nama,
                'no_hp' => $noHp ?: null,
                'password' => bcrypt('siswa123'),
                'jenis_kelamin' => strtoupper($jk),
                'kelas_reguler_id' => $kelasReguler->id,
                'kelas_tartil_id' => null,
                'tanggal_lahir' => $parsedTglLahir,
                'tempat_lahir' => $tempatLahir,
                'nama_ayah' => $namaAyah,
                'tanggal_masuk' => self::parseTanggal($tglMasuk) ?: $now,
                'status' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $semesterSiswaData[$nis] = $kelasReguler->id;
            $sukses++;
        }

        if (empty($insertData)) {
            throw new \InvalidArgumentException('Tidak ada data siswa yang valid untuk diimport.');
        }

        DB::beginTransaction();

        try {
            foreach (array_chunk($insertData, 500) as $chunk) {
                Siswa::insert($chunk);
            }

            if ($semesterAktif) {
                $nisList = array_column($insertData, 'nis');
                $siswaBaru = Siswa::whereIn('nis', $nisList)->get()->keyBy('nis');

                foreach ($insertData as $data) {
                    $siswa = $siswaBaru->get($data['nis']);
                    if (! $siswa) {
                        continue;
                    }

                    SemesterSiswa::create([
                        'semester_id' => $semesterAktif->id,
                        'siswa_id' => $siswa->id,
                        'kelas_id' => null,
                        'kelas_reguler_id' => $semesterSiswaData[$data['nis']],
                        'status_siswa' => 'aktif',
                        'keterangan' => 'Siswa baru via command',
                    ]);
                }
            }

            DB::commit();

            Log::info('Import siswa', [
                'sukses' => $sukses,
                'gagal' => $gagal,
                'file' => $path,
            ]);

            return [
                'sukses' => $sukses,
                'gagal' => $gagal,
                'errors' => $errors,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal import siswa', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private static function parseTanggal(?string $val): ?string
    {
        if (! $val) {
            return null;
        }
        if (is_numeric($val)) {
            return Date::excelToDateTimeObject($val)->format('Y-m-d');
        }
        $d = \DateTime::createFromFormat('Y-m-d', $val);

        return $d ? $d->format('Y-m-d') : null;
    }
}
