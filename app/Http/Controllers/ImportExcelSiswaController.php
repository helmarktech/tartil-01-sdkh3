<?php

namespace App\Http\Controllers;

use App\Models\KelasReguler;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExcelSiswaController extends Controller
{
    // ═══════════════════════════════════════════════════
    // HALAMAN IMPORT
    // ═══════════════════════════════════════════════════
    public function index()
    {
        return view('admin.siswa.import');
    }

    // ═══════════════════════════════════════════════════
    // PROSES IMPORT
    // ═══════════════════════════════════════════════════
    public function proses(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'Format file harus xlsx, xls, atau csv.',
            'file.max' => 'Ukuran file maksimal 2MB.',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Header = baris pertama
            if (count($rows) < 2) {
                return redirect()->back()->with('error', 'File kosong atau tidak memiliki data.');
            }

            $header = array_map('strtoupper', array_map('trim', $rows[0]));
            $required = ['NIS', 'NAMA', 'JENIS_KELAMIN', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT'];
            $missing = array_diff($required, $header);
            if (!empty($missing)) {
                return redirect()->back()->with('error', 'Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '. Pastikan header baris pertama sesuai.');
            }

            $colIdx = [];
            foreach ($required as $col) {
                $colIdx[$col] = array_search($col, $header);
            }
            $colIdx['NO_HP'] = array_search('NO_HP', $header);
            $colIdx['TANGGAL_MASUK'] = array_search('TANGGAL_MASUK', $header);

            $sukses = 0;
            $gagal = 0;
            $errors = [];
            $insertData = [];
            $now = now();

            // Ambil semua kelas reguler aktif untuk validasi
            $kelasRegulers = KelasReguler::where('is_aktif', true)->get()->keyBy(function ($k) {
                return strtoupper(trim($k->nama)) . '|' . (int)$k->jenjang . '|' . strtoupper(trim($k->tingkat));
            });

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty(array_filter($row))) continue; // Skip baris kosong

                $nis = trim($row[$colIdx['NIS']] ?? '');
                $nama = trim($row[$colIdx['NAMA']] ?? '');
                $jk = trim($row[$colIdx['JENIS_KELAMIN']] ?? '');
                $kelasNama = trim($row[$colIdx['KELAS_NAMA']] ?? '');
                $kelasJenjang = trim($row[$colIdx['KELAS_JENJANG']] ?? '');
                $kelasTingkat = trim($row[$colIdx['KELAS_TINGKAT']] ?? '');
                $noHp = $colIdx['NO_HP'] !== false ? trim($row[$colIdx['NO_HP']] ?? '') : '';
                $tglMasuk = $colIdx['TANGGAL_MASUK'] !== false ? trim($row[$colIdx['TANGGAL_MASUK']] ?? '') : $now->format('Y-m-d');

                // ── Validasi wajib ──
                if (!$nis) { $errors[] = 'Baris ' . ($i + 1) . ': NIS wajib diisi.'; $gagal++; continue; }
                if (!$nama) { $errors[] = 'Baris ' . ($i + 1) . ': Nama wajib diisi.'; $gagal++; continue; }
                if (!$jk) { $errors[] = 'Baris ' . ($i + 1) . ': Jenis Kelamin wajib diisi (L/P).'; $gagal++; continue; }
                if (!in_array(strtoupper($jk), ['L', 'P'])) { $errors[] = 'Baris ' . ($i + 1) . ': Jenis Kelamin harus L atau P.'; $gagal++; continue; }
                if (!$kelasNama || !$kelasJenjang || !$kelasTingkat) { $errors[] = 'Baris ' . ($i + 1) . ': Kelas (nama, jenjang, tingkat) wajib diisi.'; $gagal++; continue; }

                // ── Validasi kelas reguler ──
                $key = strtoupper($kelasNama) . '|' . (int)$kelasJenjang . '|' . strtoupper($kelasTingkat);
                $kelasReguler = $kelasRegulers->get($key);

                if (!$kelasReguler) {
                    $errors[] = 'Baris ' . ($i + 1) . ': Kelas "' . $kelasNama . ' jenjang ' . $kelasJenjang . ' tingkat ' . $kelasTingkat . '" tidak ditemukan di database. Pastikan data kelas sudah dibuat.';
                    $gagal++;
                    continue;
                }

                // ── Cek duplikat NIS ──
                if (Siswa::where('nis', $nis)->exists()) {
                    $errors[] = 'Baris ' . ($i + 1) . ': NIS "' . $nis . '" sudah terdaftar.';
                    $gagal++;
                    continue;
                }

                // ── Siapkan data ──
                $insertData[] = [
                    'nis' => $nis,
                    'nama' => $nama,
                    'no_hp' => $noHp ?: null,
                    'password' => bcrypt('siswa123'),
                    'jenis_kelamin' => strtoupper($jk),
                    'kelas_reguler_id' => $kelasReguler->id,
                    'kelas_tartil_id' => null, // Tartil bisa diisi nanti via Penempatan
                    'tanggal_masuk' => $this->parseTanggal($tglMasuk) ?: $now,
                    'status' => 'aktif',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $sukses++;
            }

            // ── Insert batch ──
            if (!empty($insertData)) {
                foreach (array_chunk($insertData, 500) as $chunk) {
                    Siswa::insert($chunk);
                }
            }

            Log::info('Import siswa dari Excel', [
                'sukses' => $sukses,
                'gagal' => $gagal,
                'admin_id' => auth()->id(),
            ]);

            $msg = 'Import selesai: ' . $sukses . ' siswa berhasil' . ($gagal > 0 ? ', ' . $gagal . ' gagal.' : '.');
            if (!empty($errors)) {
                return redirect()->route('admin.siswa.import')
                    ->with('warning', $msg)
                    ->with('import_errors', array_slice($errors, 0, 20));
            }
            return redirect()->route('admin.siswa.import')->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Gagal import siswa', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // DOWNLOAD TEMPLATE
    // ═══════════════════════════════════════════════════
    public function template()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['NIS', 'NAMA', 'JENIS_KELAMIN', 'NO_HP', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT', 'TANGGAL_MASUK'];
        $sheet->fromArray($headers, null, 'A1');

        // Contoh data
        $sheet->fromArray(['2026001', 'Ahmad Fauzi', 'L', '08123456789', '1A', '1', 'A', date('Y-m-d')], null, 'A2');
        $sheet->fromArray(['2026002', 'Siti Aminah', 'P', '08123456788', '1A', '1', 'A', date('Y-m-d')], null, 'A3');
        $sheet->fromArray(['2026003', 'Budi Santoso', 'L', '08123456787', '2B', '2', 'B', date('Y-m-d')], null, 'A4');

        // Styling header
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setWidth(16);
        }
        $sheet->getColumnDimension('B')->setWidth(25);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template-import-siswa.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    // ═══════════════════════════════════════════════════
    // Helpers
    // ═══════════════════════════════════════════════════
    private function parseTanggal(?string $val): ?string
    {
        if (!$val) return null;
        if (is_numeric($val)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)->format('Y-m-d');
        }
        $d = \DateTime::createFromFormat('Y-m-d', $val);
        return $d ? $d->format('Y-m-d') : null;
    }
}
