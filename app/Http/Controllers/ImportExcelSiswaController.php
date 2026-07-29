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
            $required = ['NIS', 'NAMA', 'JENIS_KELAMIN', 'KELAS_NAMA', 'KELAS_JENJANG', 'KELAS_TINGKAT'];
            $missing = array_diff($required, $header);
            if (! empty($missing)) {
                return redirect()->back()->with('error', 'Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Pastikan header baris pertama sesuai.');
            }

            // Simpan file ke storage agar queue worker bisa mengaksesnya
            $path = $file->storeAs('imports/siswa', date('YmdHis').'_'.preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName), 'local');
            $fullPath = storage_path('app/'.$path);

            ImportSiswaJob::dispatch($fullPath, auth()->id());

            Log::info('Import siswa di-queue', [
                'file' => $fullPath,
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
