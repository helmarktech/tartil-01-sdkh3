<?php

namespace Database\Seeders;

use App\Models\KelasReguler;
use App\Models\Semester;
use App\Models\SemesterSiswa;
use App\Models\Siswa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

/**
 * Seeder import data siswa dari file template-import-siswa (3).xlsx.
 *
 * Format Excel yang diharapkan:
 *   NIS | NAMA | JENIS_KELAMIN | NO_HP | KELAS_NAMA | KELAS_JENJANG | KELAS_TINGKAT | TANGGAL_MASUK
 *
 * Kelas reguler yang belum ada akan dibuat otomatis.
 * Jika semester aktif ada, siswa akan langsung terdaftar di semester aktif.
 */
class ImportSiswaSeeder extends Seeder
{
    /**
     * Path relatif ke file Excel siswa (dari root project).
     */
    private string $filePath = 'template-import-siswa (3).xlsx';

    public function run(): void
    {
        $path = base_path($this->filePath);

        if (! file_exists($path)) {
            $this->command->error("File tidak ditemukan: {$path}");

            return;
        }

        $semesterAktif = Semester::aktif()->first();
        if (! $semesterAktif) {
            $this->command->warn('Tidak ada semester aktif. Siswa tetap diimport tanpa pendaftaran semester.');
        }

        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (count($rows) < 2) {
            $this->command->error('File kosong atau tidak memiliki data.');

            return;
        }

        $header = array_map('strtoupper', array_map('trim', $rows[0]));
        $required = ['NIS', 'NAMA', 'JENIS_KELAMIN', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT'];
        $missing = array_diff($required, $header);
        if (! empty($missing)) {
            $this->command->error('Kolom wajib tidak ditemukan: '.implode(', ', $missing));

            return;
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
        $semesterKelasUpdates = [];

        // Preload kelas reguler agar tidak query berulang kali
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
                $this->command->warn('Baris '.($i + 1).': NO_HP kosong, diisi default 000000000000 untuk NIS '.$nis.'.');
            }
            $tglMasuk = $colIdx['TANGGAL_MASUK'] !== false ? trim((string) ($row[$colIdx['TANGGAL_MASUK']] ?? '')) : $now->format('Y-m-d');

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

            $key = strtoupper($kelasNama).'|'.(int) $kelasJenjang.'|'.strtoupper($kelasTingkat);

            if (! $kelasRegulers->has($key)) {
                // Buat kelas reguler baru jika belum ada
                $kelasReguler = KelasReguler::create([
                    'nama' => strtoupper($kelasNama),
                    'jenjang' => (int) $kelasJenjang,
                    'tingkat' => strtoupper($kelasTingkat),
                    'is_aktif' => true,
                ]);
                $kelasRegulers->put($key, $kelasReguler);
                $this->command->info("Kelas reguler baru dibuat: {$kelasReguler->nama}");
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
                'tanggal_masuk' => $this->parseTanggal($tglMasuk) ?: $now,
                'status' => 'aktif',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $semesterSiswaData[$nis] = $kelasReguler->id;
            $sukses++;
        }

        if (empty($insertData)) {
            $this->command->warn('Tidak ada data siswa yang valid untuk diimport.');

            return;
        }

        DB::beginTransaction();

        try {
            foreach (array_chunk($insertData, 500) as $chunk) {
                Siswa::insert($chunk);
            }

            // Sinkronkan ke semester aktif
            if ($semesterAktif) {
                $nisList = array_column($insertData, 'nis');
                $siswaBaru = Siswa::whereIn('nis', $nisList)->get()->keyBy('nis');

                foreach ($insertData as $data) {
                    $siswa = $siswaBaru->get($data['nis']);
                    if (! $siswa) {
                        continue;
                    }

                    $kelasRegulerId = $semesterSiswaData[$data['nis']];

                    SemesterSiswa::create([
                        'semester_id' => $semesterAktif->id,
                        'siswa_id' => $siswa->id,
                        'kelas_id' => null,
                        'kelas_reguler_id' => $kelasRegulerId,
                        'status_siswa' => 'aktif',
                        'keterangan' => 'Siswa baru via seeder',
                    ]);
                }
            }

            DB::commit();

            Log::info('Import siswa via seeder', [
                'sukses' => $sukses,
                'gagal' => $gagal,
                'file' => $this->filePath,
            ]);

            $this->command->info('Import siswa selesai.');
            $this->command->info("  Sukses: {$sukses}");
            $this->command->info("  Gagal: {$gagal}");

            if (! empty($errors)) {
                $this->command->warn('Detail error (20 pertama):');
                foreach (array_slice($errors, 0, 20) as $err) {
                    $this->command->warn('  - '.$err);
                }
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Gagal import siswa via seeder', ['error' => $e->getMessage()]);
            $this->command->error('Gagal: '.$e->getMessage());
            throw $e;
        }
    }

    private function parseTanggal(?string $val): ?string
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
