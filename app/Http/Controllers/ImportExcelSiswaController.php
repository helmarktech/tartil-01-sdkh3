<?php

namespace App\Http\Controllers;

use App\Jobs\ImportSiswaJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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
        $originalName = $file->getClientOriginalName();

        try {
            // Validasi header sebelum dispatch (cepat, tidak memproses seluruh data)
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return redirect()->back()->with('error', 'File kosong atau tidak memiliki data.');
            }

            $header = array_map('strtoupper', array_map('trim', $rows[0]));
            $required = ['NIS', 'NAMA', 'JENIS_KELAMIN', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT', 'TANGGAL_LAHIR', 'TEMPAT_LAHIR', 'NAMA_AYAH'];
            $missing = array_diff($required, $header);
            if (! empty($missing)) {
                return redirect()->back()->with('error', 'Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Pastikan header baris pertama sesuai.');
            }

            // Baca konten file dan encode ke base64 agar bisa dibawa oleh job queue.
            // Database queue menyimpan payload sebagai JSON; binary Excel tidak bisa
            // di-encode langsung. Base64 memastikan worker di instance lain tetap
            // bisa merekonstruksi file Excel yang sama.
            $fileContent = base64_encode(file_get_contents($file->getPathname()));

            ImportSiswaJob::dispatch($fileContent, auth()->id(), $originalName);

            Log::info('Import siswa di-queue', [
                'file' => $originalName,
                'admin_id' => auth()->id(),
                'rows' => count($rows) - 1,
            ]);

            return redirect()->route('admin.siswa.import')
                ->with('success', 'File "'.$originalName.'" berhasil diunggah. Import '.(count($rows) - 1).' siswa sedang diproses di background oleh queue worker. Hasil bisa dicek di log aplikasi.');

        } catch (\Exception $e) {
            Log::error('Gagal queue import siswa', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Gagal memproses file: '.$e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // DOWNLOAD TEMPLATE
    // ═══════════════════════════════════════════════════
    public function template()
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = ['NIS', 'NAMA', 'JENIS_KELAMIN', 'NO_HP', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT', 'TANGGAL_MASUK', 'TANGGAL_LAHIR', 'TEMPAT_LAHIR', 'NAMA_AYAH'];
        $sheet->fromArray($headers, null, 'A1');

        // Contoh data
        $sheet->fromArray(['2026001', 'Ahmad Fauzi', 'L', '08123456789', '1A', '1', 'A', date('Y-m-d'), '2015-01-15', 'Surabaya', 'Bapak Fauzi'], null, 'A2');
        $sheet->fromArray(['2026002', 'Siti Aminah', 'P', '08123456788', '1A', '1', 'A', date('Y-m-d'), '2015-03-22', 'Sidoarjo', 'Bapak Amin'], null, 'A3');
        $sheet->fromArray(['2026003', 'Budi Santoso', 'L', '08123456787', '2B', '2', 'B', date('Y-m-d'), '2014-07-10', 'Gresik', 'Bapak Santoso'], null, 'A4');

        // Styling header
        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setWidth(16);
        }
        $sheet->getColumnDimension('B')->setWidth(25);
        $sheet->getColumnDimension('J')->setWidth(18);
        $sheet->getColumnDimension('K')->setWidth(20);

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
