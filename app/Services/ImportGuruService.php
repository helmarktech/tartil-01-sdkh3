<?php

namespace App\Services;

use App\Models\GuruReguler;
use App\Models\GuruTartil;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Output\OutputInterface;

class ImportGuruService
{
    public static function process(string $path, string $jenis, OutputInterface $output): array
    {
        if (! file_exists($path)) {
            throw new \InvalidArgumentException("File tidak ditemukan: {$path}");
        }

        if (! in_array($jenis, ['reguler', 'tartil'])) {
            throw new \InvalidArgumentException("Jenis guru harus reguler atau tartil. Diberikan: {$jenis}");
        }

        $isTartil = $jenis === 'tartil';

        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        if (count($rows) < 2) {
            throw new \InvalidArgumentException('File kosong atau tidak memiliki data.');
        }

        $header = array_map('strtoupper', array_map('trim', $rows[0]));
        $required = ['NAMA', 'EMAIL', 'NO_HP', 'JENIS_KELAMIN'];
        $missing = array_diff($required, $header);
        if (! empty($missing)) {
            throw new \InvalidArgumentException('Kolom wajib tidak ditemukan: '.implode(', ', $missing));
        }

        $colIdx = [];
        foreach ($required as $col) {
            $colIdx[$col] = array_search($col, $header);
        }
        $colIdx['NIP'] = array_search('NIP', $header);
        $colIdx['ALAMAT'] = array_search('ALAMAT', $header);

        $sukses = 0;
        $gagal = 0;
        $errors = [];
        $insertGuru = [];
        $now = now();

        $model = $isTartil ? GuruTartil::class : GuruReguler::class;

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            if (empty(array_filter($row))) {
                continue;
            }

            $nama = trim((string) ($row[$colIdx['NAMA']] ?? ''));
            $email = trim((string) ($row[$colIdx['EMAIL']] ?? ''));
            $noHp = trim((string) ($row[$colIdx['NO_HP']] ?? ''));
            $jk = trim((string) ($row[$colIdx['JENIS_KELAMIN']] ?? ''));
            $nip = $colIdx['NIP'] !== false ? trim((string) ($row[$colIdx['NIP']] ?? '')) : '';
            $alamat = $colIdx['ALAMAT'] !== false ? trim((string) ($row[$colIdx['ALAMAT']] ?? '')) : '';

            if (! $nama) {
                $errors[] = 'Baris '.($i + 1).': Nama wajib diisi.';
                $gagal++;

                continue;
            }
            if (! $email) {
                $errors[] = 'Baris '.($i + 1).': Email wajib diisi.';
                $gagal++;

                continue;
            }
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Baris '.($i + 1).': Format email tidak valid.';
                $gagal++;

                continue;
            }
            if (! $noHp) {
                $errors[] = 'Baris '.($i + 1).': No HP wajib diisi.';
                $gagal++;

                continue;
            }
            if (! $jk) {
                $errors[] = 'Baris '.($i + 1).': Jenis Kelamin wajib diisi (L/P).';
                $gagal++;

                continue;
            }
            if (! in_array(strtoupper($jk), ['L', 'P'])) {
                $errors[] = 'Baris '.($i + 1).': Jenis Kelamin harus L atau P.';
                $gagal++;

                continue;
            }

            $emailUpper = strtoupper($email);
            $nipUpper = $nip ? strtoupper($nip) : null;

            if ($model::whereRaw('UPPER(email) = ?', [$emailUpper])->exists()) {
                $errors[] = 'Baris '.($i + 1).': Email "'.$email.'" sudah terdaftar sebagai guru '.$jenis.'.';
                $gagal++;

                continue;
            }

            if ($nipUpper) {
                if ($model::whereRaw('UPPER(nip) = ?', [$nipUpper])->exists()) {
                    $errors[] = 'Baris '.($i + 1).': NIP "'.$nip.'" sudah terdaftar sebagai guru '.$jenis.'.';
                    $gagal++;

                    continue;
                }
            }

            if ($isTartil && User::whereRaw('UPPER(email) = ?', [$emailUpper])->exists()) {
                $errors[] = 'Baris '.($i + 1).': Email "'.$email.'" sudah digunakan sebagai akun login.';
                $gagal++;

                continue;
            }

            $insertGuru[] = [
                'nama' => $nama,
                'email' => $email,
                'no_hp' => $noHp,
                'jenis_kelamin' => strtoupper($jk),
                'nip' => $nip ?: null,
                'alamat' => $alamat ?: null,
                'is_aktif' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $sukses++;
        }

        if (! empty($insertGuru)) {
            DB::transaction(function () use ($insertGuru, $isTartil) {
                foreach (array_chunk($insertGuru, 500) as $chunk) {
                    if ($isTartil) {
                        GuruTartil::insert($chunk);
                    } else {
                        GuruReguler::insert($chunk);
                    }
                }

                if ($isTartil) {
                    $emails = array_map(fn ($g) => strtoupper($g['email']), $insertGuru);
                    $guruBaru = GuruTartil::whereIn(DB::raw('UPPER(email)'), $emails)
                        ->orderBy('id')
                        ->get(['id', 'nama', 'email']);

                    $insertUsers = [];
                    $now = now();
                    foreach ($guruBaru as $guru) {
                        $insertUsers[] = [
                            'nama' => $guru->nama,
                            'email' => $guru->email,
                            'password' => Hash::make('guru123'),
                            'role' => 'guru',
                            'guru_id' => $guru->id,
                            'is_aktif' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if (! empty($insertUsers)) {
                        foreach (array_chunk($insertUsers, 500) as $chunk) {
                            User::insert($chunk);
                        }
                    }
                }
            });
        }

        Log::info('Import guru dari Excel', [
            'jenis' => $jenis,
            'sukses' => $sukses,
            'gagal' => $gagal,
        ]);

        return [
            'sukses' => $sukses,
            'gagal' => $gagal,
            'errors' => $errors,
        ];
    }
}
