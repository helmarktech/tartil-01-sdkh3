<?php

namespace App\Http\Controllers;

use App\Jobs\ImportGuruJob;
use Illuminate\Http\Request;
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
        $originalName = $file->getClientOriginalName();

        try {
            // Validasi header sebelum dispatch
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            if (count($rows) < 2) {
                return redirect()->back()->with('error', 'File kosong atau tidak memiliki data.');
            }

            $header = array_map('strtoupper', array_map('trim', $rows[0]));
            $required = ['NAMA', 'EMAIL', 'NO_HP', 'JENIS_KELAMIN'];
            $missing = array_diff($required, $header);
            if (! empty($missing)) {
                return redirect()->back()->with('error', 'Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Pastikan header baris pertama sesuai.');
            }

            // Baca konten file dan encode ke base64 agar bisa dibawa oleh job queue.
            // Database queue menyimpan payload sebagai JSON; binary Excel tidak bisa
            // di-encode langsung. Base64 memastikan worker di instance lain tetap
            // bisa merekonstruksi file Excel yang sama.
            $fileContent = base64_encode(file_get_contents($file->getPathname()));

            ImportGuruJob::dispatch($fileContent, $jenis, auth()->id(), $originalName);

            Log::info('Import guru di-queue', [
                'file' => $originalName,
                'jenis' => $jenis,
                'admin_id' => auth()->id(),
                'rows' => count($rows) - 1,
            ]);

            return redirect()->route('admin.guru.import', ['jenis' => $jenis])
                ->with('success', 'File "'.$originalName.'" berhasil diunggah. Import '.(count($rows) - 1).' guru '.($jenis === 'tartil' ? 'tartil' : 'reguler').' sedang diproses di background oleh queue worker. Hasil bisa dicek di log aplikasi.');

        } catch (\Exception $e) {
            Log::error('Gagal queue import guru', ['error' => $e->getMessage(), 'jenis' => $jenis]);

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
