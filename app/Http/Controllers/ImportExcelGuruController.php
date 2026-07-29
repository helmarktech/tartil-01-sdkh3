<?php

namespace App\Http\Controllers;

use App\Models\GuruReguler;
use App\Models\GuruTartil;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ImportExcelGuruController extends Controller
{
    // ═══════════════════════════════════════════════════
    // HALAMAN IMPORT
    // ═══════════════════════════════════════════════════
    public function index(Request $request)
    {
        $jenis = $request->get('jenis', 'tartil');

        return view('admin.guru.import', compact('jenis'));
    }

    // ═══════════════════════════════════════════════════
    // PROSES IMPORT
    // ═══════════════════════════════════════════════════
    public function proses(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            'jenis' => 'required|in:reguler,tartil',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'Format file harus xlsx, xls, atau csv.',
            'file.max' => 'Ukuran file maksimal 2MB.',
            'jenis.required' => 'Jenis guru wajib dipilih.',
            'jenis.in' => 'Jenis guru harus Reguler atau Tartil.',
        ]);

        $file = $request->file('file');
        $jenis = $request->input('jenis');
        $isTartil = $jenis === 'tartil';

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Header = baris pertama
            if (count($rows) < 2) {
                return redirect()->back()->with('error', 'File kosong atau tidak memiliki data.');
            }

            $header = array_map('strtoupper', array_map('trim', $rows[0]));
            $required = ['NAMA', 'EMAIL', 'NO_HP', 'JENIS_KELAMIN'];
            $missing = array_diff($required, $header);
            if (! empty($missing)) {
                return redirect()->back()->with('error', 'Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Pastikan header baris pertama sesuai.');
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
            $insertUsers = [];
            $now = now();

            $model = $isTartil ? GuruTartil::class : GuruReguler::class;

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) {
                    continue; // Skip baris kosong
                }

                $nama = trim($row[$colIdx['NAMA']] ?? '');
                $email = trim($row[$colIdx['EMAIL']] ?? '');
                $noHp = trim($row[$colIdx['NO_HP']] ?? '');
                $jk = trim($row[$colIdx['JENIS_KELAMIN']] ?? '');
                $nip = $colIdx['NIP'] !== false ? trim($row[$colIdx['NIP']] ?? '') : '';
                $alamat = $colIdx['ALAMAT'] !== false ? trim($row[$colIdx['ALAMAT']] ?? '') : '';

                // ── Validasi wajib ──
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

                // ── Cek duplikat email di tabel masing-masing ──
                if ($model::whereRaw('UPPER(email) = ?', [$emailUpper])->exists()) {
                    $errors[] = 'Baris '.($i + 1).': Email "'.$email.'" sudah terdaftar sebagai guru '.$jenis.'.';
                    $gagal++;

                    continue;
                }

                // ── Cek duplikat NIP jika diisi ──
                if ($nipUpper) {
                    if ($model::whereRaw('UPPER(nip) = ?', [$nipUpper])->exists()) {
                        $errors[] = 'Baris '.($i + 1).': NIP "'.$nip.'" sudah terdaftar sebagai guru '.$jenis.'.';
                        $gagal++;

                        continue;
                    }
                }

                // ── Cek duplikat email di tabel users untuk guru tartil ──
                if ($isTartil && User::whereRaw('UPPER(email) = ?', [$emailUpper])->exists()) {
                    $errors[] = 'Baris '.($i + 1).': Email "'.$email.'" sudah digunakan sebagai akun login.';
                    $gagal++;

                    continue;
                }

                $guruData = [
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

                $insertGuru[] = $guruData;
                $sukses++;
            }

            // ── Insert batch ──
            if (! empty($insertGuru)) {
                DB::transaction(function () use ($insertGuru, $isTartil, &$insertUsers) {
                    foreach (array_chunk($insertGuru, 500) as $chunk) {
                        if ($isTartil) {
                            GuruTartil::insert($chunk);
                        } else {
                            GuruReguler::insert($chunk);
                        }
                    }

                    // Jika guru tartil, buatkan akun login untuk setiap guru yang baru diinsert
                    if ($isTartil) {
                        // Ambil ID guru yang baru diinsert berdasarkan email
                        $emails = array_map(fn ($g) => strtoupper($g['email']), $insertGuru);
                        $guruBaru = GuruTartil::whereIn(DB::raw('UPPER(email)'), $emails)
                            ->orderBy('id')
                            ->get(['id', 'nama', 'email']);

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
                'admin_id' => auth()->id(),
            ]);

            $msg = 'Import selesai: '.$sukses.' guru '.($isTartil ? 'tartil' : 'reguler').' berhasil'.($gagal > 0 ? ', '.$gagal.' gagal.' : '.');
            if ($isTartil && $sukses > 0) {
                $msg .= ' Akun login dengan password default `guru123` sudah dibuat.';
            }

            $redirect = redirect()->route('admin.guru.import', ['jenis' => $jenis]);
            if (! empty($errors)) {
                return $redirect
                    ->with('warning', $msg)
                    ->with('import_errors', array_slice($errors, 0, 20));
            }

            return $redirect->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Gagal import guru', ['error' => $e->getMessage(), 'jenis' => $jenis]);

            return redirect()->back()->with('error', 'Gagal memproses file: '.$e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // DOWNLOAD TEMPLATE
    // ═══════════════════════════════════════════════════
    public function template(Request $request)
    {
        $jenis = $request->get('jenis', 'tartil');
        $isTartil = $jenis === 'tartil';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['NIP', 'NAMA', 'EMAIL', 'NO_HP', 'JENIS_KELAMIN', 'ALAMAT'];
        $sheet->fromArray($headers, null, 'A1');

        // Contoh data
        if ($isTartil) {
            $sheet->fromArray(['GT001', 'Ust. Ahmad Fauzi', 'ahmad@tartil.id', '08123456789', 'L', 'Jl. Mawar No. 1'], null, 'A2');
            $sheet->fromArray(['GT002', 'Ust. Budi Santoso', 'budi@tartil.id', '08123456788', 'L', 'Jl. Melati No. 2'], null, 'A3');
        } else {
            $sheet->fromArray(['GR001', 'Ahmad Fauzi', 'ahmad.reguler@tartil.id', '08123456789', 'L', 'Jl. Mawar No. 1'], null, 'A2');
            $sheet->fromArray(['GR002', 'Siti Aminah', 'siti.reguler@tartil.id', '08123456788', 'P', 'Jl. Melati No. 2'], null, 'A3');
        }

        // Styling header
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setWidth(18);
        }
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('F')->setWidth(30);

        $filename = 'template-import-guru-'.$jenis.'.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }
}
