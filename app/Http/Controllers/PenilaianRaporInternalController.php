<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\IndikatorPenilaian;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\KopSuratRapor;
use App\Models\PenilaianRaporInternal;
use App\Models\PenilaianRaporNilai;
use App\Models\RekapR2Akhir;
use App\Models\Semester;
use App\Models\Siswa;
use App\Services\JurnalSiswaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenilaianRaporInternalController extends Controller
{
    // ═══════════════════════════════════════════════
    // ADMIN: INDEX
    // ═══════════════════════════════════════════════
    public function adminIndex()
    {
        $penilaians = PenilaianRaporInternal::with('semester')
            ->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.penilaian-rapor-internal.index', compact('penilaians'));
    }

    // ═══════════════════════════════════════════════
    // ADMIN: STORE
    // ═══════════════════════════════════════════════
    public function adminStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'semester_id' => 'required|exists:semesters,id|unique:penilaian_rapor_internals,semester_id',
        ], [
            'semester_id.unique' => 'Semester ini sudah memiliki penilaian rapor. Hanya 1 penilaian per semester.',
        ]);

        try {
            PenilaianRaporInternal::create([
                'nama' => $request->nama,
                'semester_id' => $request->semester_id,
                'status' => 'aktif',
            ]);

            return back()->with('success', 'Penilaian rapor internal berhasil dibuat.');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate') || str_contains($e->getMessage(), 'unique')) {
                return back()->with('error', 'Semester ini sudah memiliki penilaian rapor. Hanya 1 penilaian per semester.');
            }
            throw $e;
        }
    }

    // ═══════════════════════════════════════════════
    // ADMIN: HAPUS
    // ═══════════════════════════════════════════════
    public function adminDestroy(PenilaianRaporInternal $penilaian)
    {
        try {
            $penilaian->delete();

            return back()->with('success', 'Penilaian dihapus.');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'foreign key')) {
                return back()->with('error', 'Penilaian tidak dapat dihapus karena masih terkait dengan data lain.');
            }
            throw $e;
        }
    }

    // ═══════════════════════════════════════════════
    // GURU: INDEX
    // ═══════════════════════════════════════════════
    public function guruIndex()
    {
        $penilaians = PenilaianRaporInternal::with('semester')
            ->where('status', 'aktif')
            ->orderBy('created_at', 'desc')->get();

        return view('guru.penilaian-rapor-internal.index', compact('penilaians'));
    }

    // ═══════════════════════════════════════════════
    // STEP 1: GURU PILIH KELAS
    // ═══════════════════════════════════════════════
    public function guruPilihKelas(PenilaianRaporInternal $penilaian)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }

        $kelasList = Kelas::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        if ($kelasList->isEmpty()) {
            return redirect()->route('guru.penilaian-rapor-internal.index')
                ->with('error', 'Anda tidak memiliki kelas aktif.');
        }

        // Hitung progress per kelas
        $siswaIds = Siswa::whereIn('kelas_tartil_id', $kelasList->pluck('id'))
            ->where('status', 'aktif')
            ->pluck('id');

        $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
            ->whereIn('siswa_id', $siswaIds)
            ->whereNotNull('nilai')
            ->select('siswa_id')
            ->distinct()
            ->pluck('siswa_id');

        foreach ($kelasList as $kelas) {
            $siswaKelasIds = Siswa::where('kelas_tartil_id', $kelas->id)
                ->where('status', 'aktif')
                ->pluck('id');
            $total = $siswaKelasIds->count();

            $indikatorCount = IndikatorPenilaian::byJenis($kelas->jenis)->count();
            $siswaLengkap = 0;
            foreach ($siswaKelasIds as $sid) {
                $diisi = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
                    ->where('siswa_id', $sid)
                    ->whereNotNull('nilai')
                    ->count();
                if ($diisi >= $indikatorCount) {
                    $siswaLengkap++;
                }
            }

            $kelas->total_siswa = $total;
            $kelas->sudah_dinilai = $siswaLengkap;
            $kelas->jumlah_indikator = $indikatorCount;
            $kelas->progress_persen = $total > 0 ? round(($siswaLengkap / $total) * 100) : 0;
        }

        return view('guru.penilaian-rapor-internal.pilih-kelas', compact('penilaian', 'kelasList'));
    }

    // ═══════════════════════════════════════════════
    // STEP 2: GURU ISI NILAI PER INDIKATOR
    // Tampil matrix: siswa × indikator (sesuai jenis kelas)
    // ═══════════════════════════════════════════════
    public function guruIsiNilai(PenilaianRaporInternal $penilaian, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }

        $kelas = Kelas::where('id', $kelasId)->where('guru_id', $guru->id)->first();
        if (! $kelas) {
            return back()->with('error', 'Kelas tidak ditemukan atau bukan kelas Anda.');
        }

        // Ambil indikator sesuai jenis kelas (byJenis sudah return get())
        $indikators = IndikatorPenilaian::byJenis($kelas->jenis);

        if ($indikators->isEmpty()) {
            return back()->with('error', 'Belum ada indikator untuk jenis kelas "'.$kelas->jenis.'". Hubungi admin.');
        }

        // Ambil siswa kelas ini
        $siswas = Siswa::where('kelas_tartil_id', $kelasId)
            ->where('status', 'aktif')
            ->with('kelasReguler')
            ->orderBy('nama')
            ->get();

        if ($siswas->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa aktif di kelas ini.');
        }

        // Ambil nilai yang sudah diisi: [siswa_id][indikator_id] = nilai
        $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->get();

        $nilaiMap = [];
        foreach ($nilaiRows as $nr) {
            $nilaiMap[$nr->siswa_id][$nr->indikator_penilaian_id] = $nr->nilai;
        }

        // Hitung progress
        $totalSiswa = $siswas->count();
        $siswaLengkap = 0;
        foreach ($siswas as $s) {
            $diisi = 0;
            foreach ($indikators as $ind) {
                if (isset($nilaiMap[$s->id][$ind->id]) && $nilaiMap[$s->id][$ind->id] !== null) {
                    $diisi++;
                }
            }
            if ($diisi >= $indikators->count()) {
                $siswaLengkap++;
            }
        }
        $progress = $totalSiswa > 0 ? round(($siswaLengkap / $totalSiswa) * 100) : 0;

        return view('guru.penilaian-rapor-internal.isi-nilai', compact(
            'penilaian', 'kelas', 'siswas', 'indikators', 'nilaiMap', 'totalSiswa', 'siswaLengkap', 'progress'
        ));
    }

    // ═══════════════════════════════════════════════
    // GURU: SIMPAN NILAI PER INDIKATOR
    // ═══════════════════════════════════════════════
    public function guruSimpanNilai(Request $request, PenilaianRaporInternal $penilaian, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }

        $request->validate([
            'nilai' => 'required|array',
            'nilai.*.*' => 'nullable|integer|min:1|max:100',
        ]);

        DB::transaction(function () use ($request, $penilaian, $guru) {
            $now = now();
            foreach ($request->nilai as $siswaId => $indikatorNilais) {
                foreach ($indikatorNilais as $indikatorId => $nilai) {
                    if ($nilai === null || $nilai === '') {
                        continue;
                    }

                    PenilaianRaporNilai::updateOrCreate(
                        [
                            'penilaian_id' => $penilaian->id,
                            'siswa_id' => $siswaId,
                            'indikator_penilaian_id' => $indikatorId,
                        ],
                        [
                            'nilai' => $nilai,
                            'diisi_oleh' => $guru->id,
                            'tanggal_diisi' => $now,
                        ]
                    );
                }
            }
        });

        // Nilai rapor kini murni dari penilaian internal, jadi cache R2 siswa
        // yang nilainya baru diubah wajib dibuang agar tidak basi.
        RekapR2Akhir::whereIn('siswa_id', array_keys($request->nilai))->delete();

        return redirect()->route('guru.penilaian-rapor.isi-nilai', [$penilaian->id, $kelasId])
            ->with('success', 'Nilai berhasil disimpan.');
    }

    // ═══════════════════════════════════════════════
    // ADMIN: REKAP PROGRESS
    // STEP 1: Pilih Penilaian → STEP 2: Tampil Progress
    // ═══════════════════════════════════════════════
    public function adminRekapProgress(Request $request)
    {
        $penilaianId = $request->get('penilaian_id');

        $penilaians = PenilaianRaporInternal::with('semester')
            ->where('status', 'aktif')
            ->orderBy('created_at', 'desc')
            ->get();

        $penilaianTerpilih = null;
        $gurus = collect(); // default empty kalau belum pilih penilaian

        if ($penilaianId) {
            $penilaianTerpilih = PenilaianRaporInternal::with('semester')->find($penilaianId);

            // Hanya hitung progress kalau ada penilaian yang dipilih
            $gurus = Guru::whereHas('kelas', function ($q) {
                $q->where('status', 'aktif');
            })
                ->with(['kelas' => function ($q) {
                    $q->where('status', 'aktif')
                        ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
                        ->orderBy('nama');
                }])
                ->orderBy('nama')
                ->paginate(50); // paging 50 per halaman

            $indikatorCountMap = [];
            foreach ($gurus as $guru) {
                foreach ($guru->kelas as $kelas) {
                    $siswaIds = Siswa::where('kelas_tartil_id', $kelas->id)
                        ->where('status', 'aktif')
                        ->pluck('id');
                    $total = $siswaIds->count();

                    // Cache hitungan indikator per jenis
                    $jenis = $kelas->jenis;
                    if (! isset($indikatorCountMap[$jenis])) {
                        $indikatorCountMap[$jenis] = IndikatorPenilaian::byJenis($jenis)->count();
                    }
                    $indikatorCount = $indikatorCountMap[$jenis];

                    $siswaLengkap = 0;
                    foreach ($siswaIds as $sid) {
                        $diisi = PenilaianRaporNilai::where('penilaian_id', $penilaianId)
                            ->where('siswa_id', $sid)
                            ->whereNotNull('nilai')
                            ->count();
                        if ($diisi >= $indikatorCount) {
                            $siswaLengkap++;
                        }
                    }

                    $kelas->total_siswa = $total;
                    $kelas->sudah_dinilai = $siswaLengkap;
                    $kelas->jumlah_indikator = $indikatorCount;
                    $kelas->progress_persen = $total > 0 ? round(($siswaLengkap / $total) * 100) : 0;
                }
            }
        }

        return view('admin.penilaian-rapor-internal.rekap-progress', compact(
            'gurus', 'penilaians', 'penilaianTerpilih', 'penilaianId'
        ));
    }

    // ═══════════════════════════════════════════════
    // ADMIN: HALAMAN IMPORT NILAI VIA EXCEL
    // STEP 1: Pilih Penilaian → STEP 2: Pilih Kelas Tartil
    // ═══════════════════════════════════════════════
    public function adminImportNilai(Request $request)
    {
        $penilaians = PenilaianRaporInternal::with('semester')
            ->orderBy('created_at', 'desc')
            ->get();

        $penilaianTerpilih = null;
        $kelasList = collect();
        $kelasTerpilih = null;
        $indikators = collect();

        if ($request->filled('penilaian_id')) {
            $penilaianTerpilih = PenilaianRaporInternal::with('semester')->find($request->penilaian_id);

            if ($penilaianTerpilih) {
                // Urutan alfabetis jenis — FIELD() tidak tersedia di SQLite (testing)
                $kelasList = Kelas::where('status', 'aktif')
                    ->with('guru')
                    ->withCount(['siswas' => fn ($q) => $q->where('status', 'aktif')])
                    ->orderBy('jenis')
                    ->orderBy('nama')
                    ->get();

                if ($request->filled('kelas_id')) {
                    $kelasTerpilih = $kelasList->firstWhere('id', (int) $request->kelas_id);
                    if ($kelasTerpilih) {
                        $indikators = IndikatorPenilaian::byJenis($kelasTerpilih->jenis);
                    }
                }
            }
        }

        return view('admin.penilaian-rapor-internal.import', compact(
            'penilaians', 'penilaianTerpilih', 'kelasList', 'kelasTerpilih', 'indikators'
        ));
    }

    // ═══════════════════════════════════════════════
    // ADMIN: TEMPLATE IMPORT NILAI RAPOR
    // Seluruh siswa aktif di kelas sudah terisi otomatis;
    // kolom nilai mengikuti indikator sesuai jenis kelas.
    // ═══════════════════════════════════════════════
    public function adminTemplateImportNilai(PenilaianRaporInternal $penilaian, Kelas $kelas)
    {
        return $this->templateImportNilaiExcel($penilaian, $kelas);
    }

    // ═══════════════════════════════════════════════
    // GURU: TEMPLATE IMPORT NILAI RAPOR (kelas sendiri)
    // ═══════════════════════════════════════════════
    public function guruTemplateImportNilai(PenilaianRaporInternal $penilaian, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }
        $kelas = Kelas::where('id', $kelasId)->where('guru_id', $guru->id)->first();
        if (! $kelas) {
            return back()->with('error', 'Kelas tidak ditemukan atau bukan kelas Anda.');
        }

        return $this->templateImportNilaiExcel($penilaian, $kelas);
    }

    /**
     * Bangun file Excel template import nilai rapor untuk 1 kelas tartil.
     */
    private function templateImportNilaiExcel(PenilaianRaporInternal $penilaian, Kelas $kelas)
    {
        $indikators = IndikatorPenilaian::byJenis($kelas->jenis);
        if ($indikators->isEmpty()) {
            return back()->with('error', 'Belum ada indikator untuk jenis kelas "'.$kelas->jenis.'". Atur dulu di menu Pengaturan Kelas.');
        }

        $siswas = Siswa::where('kelas_tartil_id', $kelas->id)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        // Nilai yang sudah ada: [siswa_id][indikator_id] = nilai
        $nilaiMap = [];
        $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->whereNotNull('nilai')
            ->get();
        foreach ($nilaiRows as $nr) {
            $nilaiMap[$nr->siswa_id][$nr->indikator_penilaian_id] = $nr->nilai;
        }

        $jumlahKolom = 3 + $indikators->count();
        $kolomTerakhir = Coordinate::stringFromColumnIndex($jumlahKolom);

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Import Nilai Rapor');

        // Judul
        $sheet->mergeCells('A1:'.$kolomTerakhir.'1');
        $sheet->setCellValue('A1', 'TEMPLATE IMPORT NILAI RAPOR TARTIL');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A2:'.$kolomTerakhir.'2');
        $sheet->setCellValue('A2', $penilaian->nama.' ('.($penilaian->semester->nama ?? '-').') - Kelas '.$kelas->nama.' - '.$kelas->jenis);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A3:'.$kolomTerakhir.'3');
        $sheet->setCellValue('A3', 'Jangan mengubah kolom ID Siswa/NIS/Nama dan header kolom indikator. Isi nilai angka bulat 1-100, kosongkan jika belum dinilai.');
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9);

        // Header: kolom indikator membawa ID agar proses import tahan terhadap pengubahan urutan kolom
        $headers = ['ID Siswa', 'NIS', 'Nama Siswa'];
        foreach ($indikators as $ind) {
            $headers[] = $ind->nama_indikator.' (ID:'.$ind->id.')';
        }
        $col = 1;
        foreach ($headers as $header) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $cellRef = $colLetter.'4';
            $sheet->setCellValue($cellRef, $header);
            $sheet->getStyle($cellRef)->getFont()->setBold(true);
            $sheet->getStyle($cellRef)->getAlignment()->setHorizontal('center');
            $sheet->getStyle($cellRef)->getFill()->setFillType('solid')->getStartColor()->setRGB('F5F5F4');
            $sheet->getStyle($cellRef)->getBorders()->getAllBorders()->setBorderStyle('thin');
            $col++;
        }

        // Data siswa (prefill nilai yang sudah ada)
        $row = 5;
        foreach ($siswas as $s) {
            $sheet->setCellValue('A'.$row, $s->id);
            $sheet->setCellValueExplicit('B'.$row, (string) $s->nis, DataType::TYPE_STRING);
            $sheet->setCellValue('C'.$row, $s->nama);

            $col = 4;
            foreach ($indikators as $ind) {
                $nilai = $nilaiMap[$s->id][$ind->id] ?? null;
                if ($nilai !== null) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col).$row, $nilai);
                }
                $col++;
            }

            for ($c = 1; $c <= $jumlahKolom; $c++) {
                $sheet->getCell(Coordinate::stringFromColumnIndex($c).$row)->getStyle()->getBorders()->getAllBorders()->setBorderStyle('thin');
            }

            $row++;
        }

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(30);
        for ($c = 4; $c <= $jumlahKolom; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setWidth(22);
        }

        $filename = 'Template_Import_Nilai_Rapor_'.preg_replace('/[^A-Za-z0-9]/', '_', $kelas->nama).'.xlsx';

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="'.$filename.'"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    // ═══════════════════════════════════════════════
    // ADMIN: PROSES IMPORT NILAI RAPOR
    // Baris tidak valid dilewati dan dilaporkan per baris; baris valid tetap tersimpan.
    // Cache R2 siswa yang terpengaruh wajib dibuang agar nilai rapor tidak basi.
    // ═══════════════════════════════════════════════
    public function adminProsesImportNilai(Request $request, PenilaianRaporInternal $penilaian, Kelas $kelas)
    {
        return $this->prosesImportNilaiRapor(
            $request, $penilaian, $kelas,
            // Admin murni bukan guru — kolom diisi_oleh nullable
            auth()->user()?->guru?->id,
            'admin.penilaian-rapor-internal.import',
            ['penilaian_id' => $penilaian->id, 'kelas_id' => $kelas->id]
        );
    }

    // ═══════════════════════════════════════════════
    // GURU: PROSES IMPORT NILAI RAPOR (kelas sendiri)
    // ═══════════════════════════════════════════════
    public function guruProsesImportNilai(Request $request, PenilaianRaporInternal $penilaian, $kelasId)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }
        $kelas = Kelas::where('id', $kelasId)->where('guru_id', $guru->id)->first();
        if (! $kelas) {
            return back()->with('error', 'Kelas tidak ditemukan atau bukan kelas Anda.');
        }

        return $this->prosesImportNilaiRapor(
            $request, $penilaian, $kelas, $guru->id,
            'guru.penilaian-rapor.isi-nilai',
            [$penilaian->id, $kelas->id]
        );
    }

    /**
     * Inti proses import nilai rapor per kelas. Baris tidak valid dilewati
     * dan dilaporkan per baris; cache R2 siswa terpengaruh dibuang.
     */
    private function prosesImportNilaiRapor(Request $request, PenilaianRaporInternal $penilaian, Kelas $kelas, ?int $diisiOleh, string $redirectRoute, array $redirectParams)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048',
        ], [
            'file.required' => 'File wajib diunggah.',
            'file.mimes' => 'Format file harus xlsx atau xls.',
            'file.max' => 'Ukuran file maksimal 2MB.',
        ]);

        $indikators = IndikatorPenilaian::byJenis($kelas->jenis);
        if ($indikators->isEmpty()) {
            return back()->with('error', 'Belum ada indikator untuk jenis kelas "'.$kelas->jenis.'".');
        }
        $indikatorValid = $indikators->pluck('id')->flip(); // id => true

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray();
        } catch (\Exception $e) {
            return back()->with('error', 'File tidak dapat dibaca: '.$e->getMessage());
        }

        // Header template berada di baris ke-4 (judul 2 baris + petunjuk)
        $headerRow = $rows[3] ?? null;
        if (! $headerRow || strtoupper(trim((string) ($headerRow[0] ?? ''))) !== 'ID SISWA') {
            return back()->with('error', 'Format file tidak sesuai. Gunakan template resmi yang diunduh dari tombol "Download Template".');
        }

        // Petakan kolom indikator dari header: "Nama Indikator (ID:12)" → kolom ke-N = indikator 12
        $kolomIndikator = []; // index kolom (0-based) => indikator_id
        foreach ($headerRow as $idx => $headerCell) {
            if ($idx < 3 || $headerCell === null) {
                continue;
            }
            if (preg_match('/\(ID:(\d+)\)\s*$/', trim((string) $headerCell), $m)) {
                $indikatorId = (int) $m[1];
                if (! $indikatorValid->has($indikatorId)) {
                    return back()->with('error', "Kolom indikator '{$headerCell}' tidak dikenal untuk jenis kelas {$kelas->jenis}. Unduh ulang template terbaru.");
                }
                $kolomIndikator[$idx] = $indikatorId;
            }
        }
        if (empty($kolomIndikator)) {
            return back()->with('error', 'Tidak ada kolom indikator yang dikenali. Gunakan template resmi.');
        }

        // Siswa aktif kelas ini, di-key by id
        $siswaMap = Siswa::where('kelas_tartil_id', $kelas->id)
            ->where('status', 'aktif')
            ->get()
            ->keyBy('id');

        $sukses = 0;
        $nilaiTersimpan = 0;
        $errors = [];
        $siswaTerpengaruh = [];
        $now = now();

        DB::beginTransaction();
        try {
            foreach (array_slice($rows, 4) as $i => $row) {
                $baris = $i + 5; // nomor baris di Excel

                // Lewati baris kosong total
                if (count(array_filter($row, fn ($c) => $c !== null && trim((string) $c) !== '')) === 0) {
                    continue;
                }

                $id = trim((string) ($row[0] ?? ''));
                if (! ctype_digit($id) || ! $siswaMap->has((int) $id)) {
                    $errors[] = "Baris {$baris}: ID Siswa '{$id}' tidak ditemukan di kelas {$kelas->nama}.";

                    continue;
                }
                $siswaId = (int) $id;

                // Validasi seluruh sel nilai dulu — baris dengan sel tidak valid dilewati utuh
                $nilaiBaris = [];
                $barisValid = true;
                foreach ($kolomIndikator as $idx => $indikatorId) {
                    $raw = trim((string) ($row[$idx] ?? ''));
                    if ($raw === '') {
                        continue; // sel kosong = tidak diubah
                    }
                    if (! is_numeric($raw) || (int) $raw != (float) $raw || (int) $raw < 1 || (int) $raw > 100) {
                        $errors[] = "Baris {$baris}: Nilai '{$raw}' tidak valid. Harus angka bulat 1-100 atau dikosongkan.";
                        $barisValid = false;

                        break;
                    }
                    $nilaiBaris[$indikatorId] = (int) $raw;
                }
                if (! $barisValid) {
                    continue;
                }
                if (empty($nilaiBaris)) {
                    continue; // baris tanpa nilai baru
                }

                foreach ($nilaiBaris as $indikatorId => $nilai) {
                    PenilaianRaporNilai::updateOrCreate(
                        [
                            'penilaian_id' => $penilaian->id,
                            'siswa_id' => $siswaId,
                            'indikator_penilaian_id' => $indikatorId,
                        ],
                        [
                            'nilai' => $nilai,
                            'diisi_oleh' => $diisiOleh,
                            'tanggal_diisi' => $now,
                        ]
                    );
                    $nilaiTersimpan++;
                }
                $siswaTerpengaruh[$siswaId] = true;
                $sukses++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Import gagal diproses: '.$e->getMessage());
        }

        // Nilai rapor murni dari penilaian internal — cache R2 siswa yang
        // nilainya berubah wajib dibuang agar tidak basi (sama seperti guruSimpanNilai).
        if (! empty($siswaTerpengaruh)) {
            RekapR2Akhir::whereIn('siswa_id', array_keys($siswaTerpengaruh))->delete();
        }

        $message = "Import selesai: {$nilaiTersimpan} nilai dari {$sukses} siswa berhasil disimpan.";
        if (! empty($errors)) {
            $message .= ' '.count($errors).' baris dilewati.';
        }

        return redirect()->route($redirectRoute, $redirectParams)
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    // ═══════════════════════════════════════════════
    // GURU: REKAP NILAI
    // Format: per indikator + rata-rata + R2 Harian (info) + Nilai Rapor
    // Nilai Rapor = rata-rata Penilaian Internal (B/C/K harian tidak masuk rapor)
    // Hanya 1 penilaian per semester
    // ═══════════════════════════════════════════════
    public function guruRekapNilai(Request $request)
    {
        $guru = auth()->user()?->guru;
        if (! $guru) {
            return back()->with('error', 'Data guru tidak ditemukan.');
        }

        // Ambil 1 penilaian aktif (hanya 1 per semester)
        $penilaian = PenilaianRaporInternal::with('semester')
            ->where('status', 'aktif')
            ->first();

        // Ambil kelas guru
        $kelasList = Kelas::where('guru_id', $guru->id)
            ->where('status', 'aktif')
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        $kelasId = $request->get('kelas_id');
        $kelasTerpilih = null;
        $siswaList = collect();
        $rekapData = [];
        $indikators = collect();

        if ($kelasId && $penilaian) {
            $kelasTerpilih = $kelasList->firstWhere('id', $kelasId);
            if (! $kelasTerpilih) {
                return back()->with('error', 'Kelas tidak ditemukan.');
            }

            $siswaList = Siswa::where('kelas_tartil_id', $kelasId)
                ->where('status', 'aktif')
                ->with('kelasReguler')
                ->orderBy('nama')
                ->get();

            $indikators = IndikatorPenilaian::byJenis($kelasTerpilih->jenis);

            // Ambil semua nilai: [siswa_id][indikator_id] = nilai
            $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
                ->whereIn('siswa_id', $siswaList->pluck('id'))
                ->whereIn('indikator_penilaian_id', $indikators->pluck('id'))
                ->whereNotNull('nilai')
                ->get()
                ->groupBy(['siswa_id', 'indikator_penilaian_id']);

            foreach ($siswaList as $siswa) {
                // 1. Nilai per indikator
                $nilaiPerIndikator = [];
                foreach ($indikators as $ind) {
                    $n = $nilaiRows[$siswa->id][$ind->id] ?? collect();
                    $nilaiPerIndikator[$ind->id] = $n->isNotEmpty() ? $n->first()->nilai : null;
                }

                // 2. R2 Penilaian = rata-rata nilai indikator saja
                $nilaiFilled = array_filter($nilaiPerIndikator, fn ($v) => $v !== null);
                $r2Penilaian = count($nilaiFilled) > 0 ? round(array_sum($nilaiFilled) / count($nilaiFilled)) : 0;

                // 3. R2 Harian = sistem poin B=2, C=1, K=0 — via SSOT
                // (filter semester, hari aktif Senin-Kamis, bukan libur kelas).
                // Hanya info rekap mengaji; TIDAK masuk nilai rapor.
                $r2Harian = $penilaian->semester
                    ? JurnalSiswaService::r2Harian($siswa->id, $penilaian->semester, $kelasTerpilih->id)
                    : 0;

                // 4. Nilai Rapor (r2_akhir) = murni rata-rata Penilaian Internal
                $r2Akhir = $r2Penilaian;

                $rekapData[$siswa->id] = [
                    'nilai_per_indikator' => $nilaiPerIndikator,
                    'r2_penilaian' => $r2Penilaian,
                    'r2_harian' => $r2Harian,
                    'r2_akhir' => $r2Akhir,
                ];
            }
        }

        return view('guru.penilaian-rapor-internal.rekap-nilai', compact(
            'kelasList', 'kelasTerpilih', 'siswaList', 'penilaian', 'indikators', 'rekapData'
        ));
    }

    // ═══════════════════════════════════════════════
    // CETAK RAPOR PDF (Admin)
    // ═══════════════════════════════════════════════

    /**
     * Halaman pilih kelas untuk cetak rapor.
     * Step 1: Pilih Mode (Kelas Tartil / Kelas Reguler)
     * Step 2: Pilih Kelas
     * Step 3: Preview & Cetak
     */
    public function adminCetakRaporPilih(Request $request)
    {
        $penilaian = PenilaianRaporInternal::where('status', 'aktif')->with('semester')->first();

        if (! $penilaian) {
            return back()->with('error', 'Tidak ada penilaian rapor aktif. Silakan buat penilaian rapor terlebih dahulu.');
        }

        // Mode: 'tartil' | 'reguler'
        $mode = $request->get('mode', 'tartil');

        // Ambil daftar kelas sesuai mode
        if ($mode === 'reguler') {
            $kelasList = KelasReguler::where('is_aktif', true)
                ->orderBy('jenjang')
                ->orderBy('tingkat')
                ->orderBy('nama')
                ->get();

            $kelasId = $request->get('kelas_id');
            $kelasTerpilih = $kelasId
                ? KelasReguler::with(['siswas' => fn ($q) => $q->where('status', 'aktif')->with(['kelasTartil'])->orderBy('nama')])->find($kelasId)
                : null;

            // Ambil kelas tartil untuk setiap siswa (diperlukan untuk R2)
            $rekapSiswa = [];
            if ($kelasTerpilih && $penilaian) {
                foreach ($kelasTerpilih->siswas as $siswa) {
                    $kelasTartil = Kelas::find($siswa->kelas_tartil_id);
                    if ($kelasTartil) {
                        $rekapSiswa[$siswa->id] = $this->hitungR2Siswa($siswa, $penilaian, $kelasTartil);
                    }
                }
            }
        } else {
            $kelasList = Kelas::where('status', 'aktif')
                ->with('guru')
                ->withCount(['siswas' => fn ($q) => $q->where('status', 'aktif')])
                ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
                ->orderBy('nama')
                ->get();

            $kelasId = $request->get('kelas_id');
            $kelasTerpilih = $kelasId
                ? Kelas::with(['siswas' => fn ($q) => $q->where('status', 'aktif')->with('kelasReguler')->orderBy('nama')])->find($kelasId)
                : null;

            $rekapSiswa = [];
            if ($kelasTerpilih && $penilaian) {
                foreach ($kelasTerpilih->siswas as $siswa) {
                    $rekapSiswa[$siswa->id] = $this->hitungR2Siswa($siswa, $penilaian, $kelasTerpilih);
                }
            }
        }

        $kop = KopSuratRapor::untukSemester($penilaian->semester_id);

        return view('admin.penilaian-rapor-internal.cetak-rapor', compact(
            'penilaian', 'kelasList', 'kelasTerpilih', 'rekapSiswa', 'kop', 'mode'
        ));
    }

    /**
     * Cetak PDF rapor per siswa.
     */
    public function adminCetakRaporPdf(Request $request, Siswa $siswa)
    {
        $penilaian = PenilaianRaporInternal::where('status', 'aktif')->with('semester')->first();
        if (! $penilaian) {
            return back()->with('error', 'Tidak ada penilaian aktif.');
        }

        $kelas = Kelas::find($siswa->kelas_tartil_id);
        if (! $kelas) {
            return back()->with('error', 'Siswa tidak memiliki kelas tartil.');
        }

        $kop = KopSuratRapor::untukSemester($penilaian->semester_id);
        $rekap = $this->hitungR2Siswa($siswa, $penilaian, $kelas);

        $pdf = Pdf::loadView('pdf.rapor-tartil', compact('siswa', 'penilaian', 'kelas', 'kop', 'rekap'))
            ->setPaper('A4', 'portrait');

        $filename = 'rapor_'.preg_replace('/[^a-zA-Z0-9]/', '_', $siswa->nama).'_'.date('Ymd').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Cetak PDF rapor batch per kelas (semua siswa dalam 1 PDF).
     */
    public function adminCetakRaporKelasPdf(Request $request)
    {
        $kelasId = $request->get('kelas_id');
        if (! $kelasId) {
            return back()->with('error', 'Pilih kelas terlebih dahulu.');
        }

        $penilaian = PenilaianRaporInternal::where('status', 'aktif')->with('semester')->first();
        if (! $penilaian) {
            return back()->with('error', 'Tidak ada penilaian aktif.');
        }

        $kelas = Kelas::with(['siswas' => fn ($q) => $q->where('status', 'aktif')->orderBy('nama')])->find($kelasId);
        if (! $kelas || $kelas->siswas->count() === 0) {
            return back()->with('error', 'Kelas tidak ditemukan atau tidak ada siswa aktif.');
        }

        $kop = KopSuratRapor::untukSemester($penilaian->semester_id);

        // Hitung rekap semua siswa
        $rekapSemuaSiswa = [];
        foreach ($kelas->siswas as $siswa) {
            $rekapSemuaSiswa[$siswa->id] = [
                'siswa' => $siswa,
                'rekap' => $this->hitungR2Siswa($siswa, $penilaian, $kelas),
            ];
        }

        $pdf = Pdf::loadView('pdf.rapor-tartil-kelas', compact('kelas', 'penilaian', 'kop', 'rekapSemuaSiswa'))
            ->setPaper('A4', 'portrait')
            ->setOption('chroot', [base_path(), storage_path('app/public'), public_path()])
            ->setOption('enable_remote', true)
            ->setOption('enable_php', false);

        $filename = 'rapor_'.preg_replace('/[^a-zA-Z0-9]/', '_', $kelas->nama).'_'.date('Ymd').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Cetak PDF rapor batch per kelas reguler (semua siswa dalam 1 PDF).
     */
    public function adminCetakRaporKelasRegulerPdf(Request $request)
    {
        $kelasId = $request->get('kelas_id');
        if (! $kelasId) {
            return back()->with('error', 'Pilih kelas reguler terlebih dahulu.');
        }

        $penilaian = PenilaianRaporInternal::where('status', 'aktif')->with('semester')->first();
        if (! $penilaian) {
            return back()->with('error', 'Tidak ada penilaian aktif.');
        }

        $kelasReguler = KelasReguler::with(['siswas' => fn ($q) => $q->where('status', 'aktif')->orderBy('nama')])->find($kelasId);
        if (! $kelasReguler || $kelasReguler->siswas->count() === 0) {
            return back()->with('error', 'Kelas tidak ditemukan atau tidak ada siswa aktif.');
        }

        $kop = KopSuratRapor::untukSemester($penilaian->semester_id);

        // Hitung rekap semua siswa (perlu cari kelas tartil masing-masing)
        $rekapSemuaSiswa = [];
        foreach ($kelasReguler->siswas as $siswa) {
            $kelasTartil = Kelas::find($siswa->kelas_tartil_id);
            if ($kelasTartil) {
                $rekapSemuaSiswa[$siswa->id] = [
                    'siswa' => $siswa,
                    'rekap' => $this->hitungR2Siswa($siswa, $penilaian, $kelasTartil),
                    'kelasTartil' => $kelasTartil,
                ];
            }
        }

        $pdf = Pdf::loadView('pdf.rapor-tartil-kelas-reguler', compact('kelasReguler', 'penilaian', 'kop', 'rekapSemuaSiswa'))
            ->setPaper('A4', 'portrait')
            ->setOption('chroot', [base_path(), storage_path('app/public'), public_path()])
            ->setOption('enable_remote', true)
            ->setOption('enable_php', false);

        $filename = 'rapor_'.preg_replace('/[^a-zA-Z0-9]/', '_', $kelasReguler->nama).'_'.date('Ymd').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Hitung R2 untuk 1 siswa: R2 Harian (info) + R2 Penilaian + Nilai Rapor (r2_akhir).
     * Nilai rapor (r2_akhir) murni dari Penilaian Internal; R2 Harian tidak masuk hitungan.
     * UNTUK SKALA BESAR: pakai cache RekapR2Akhir, hanya hitung ulang kalau cache miss atau expired.
     */
    private function hitungR2Siswa(Siswa $siswa, PenilaianRaporInternal $penilaian, Kelas $kelas): array
    {
        // Coba ambil dari cache (RekapR2Akhir)
        $semester = $penilaian->semester;
        if ($semester) {
            $cached = RekapR2Akhir::where('semester_id', $semester->id)
                ->where('kelas_id', $kelas->id)
                ->where('siswa_id', $siswa->id)
                ->first();

            // Kalau cache ada dan masih fresh (< 6 jam), pakai cache
            if ($cached && $cached->last_calculated->diffInHours(now()) < 6) {
                $indikators = IndikatorPenilaian::byJenis($kelas->jenis);
                $nilaiRows = [];
                if ($cached->jumlah_terisi > 0) {
                    $rows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
                        ->where('siswa_id', $siswa->id)
                        ->whereNotNull('nilai')
                        ->pluck('nilai', 'indikator_penilaian_id');
                    foreach ($indikators as $ind) {
                        $nilaiRows[$ind->id] = [
                            'nama' => $ind->nama_indikator,
                            'nilai' => $rows[$ind->id] ?? null,
                        ];
                    }
                } else {
                    foreach ($indikators as $ind) {
                        $nilaiRows[$ind->id] = ['nama' => $ind->nama_indikator, 'nilai' => null];
                    }
                }

                return [
                    'nilai_per_indikator' => $nilaiRows,
                    'r2_penilaian' => $cached->r2_penilaian,
                    'r2_harian' => $cached->r2_harian,
                    'r2_akhir' => $cached->r2_akhir,
                    'jumlah_indikator' => $cached->jumlah_indikator,
                    'jumlah_terisi' => $cached->jumlah_terisi,
                    'is_mutasi' => $cached->is_mutasi,
                    'tanggal_masuk_kelas_tartil' => $siswa->tanggal_masuk_kelas_tartil,
                ];
            }
        }

        // Cache miss — hitung ulang via jalur resmi (RekapR2Akhir::calculateAndSave)
        // agar filter semester/kelas/hari aktif (Senin-Kamis)/libur konsisten
        // dengan r2:precalculate dan cache tidak terkontaminasi angka tanpa filter.
        $indikators = IndikatorPenilaian::byJenis($kelas->jenis);

        $nilaiRows = PenilaianRaporNilai::where('penilaian_id', $penilaian->id)
            ->where('siswa_id', $siswa->id)
            ->whereIn('indikator_penilaian_id', $indikators->pluck('id'))
            ->whereNotNull('nilai')
            ->pluck('nilai', 'indikator_penilaian_id');

        $nilaiPerIndikator = [];
        foreach ($indikators as $ind) {
            $nilaiPerIndikator[$ind->id] = [
                'nama' => $ind->nama_indikator,
                'nilai' => $nilaiRows[$ind->id] ?? null,
            ];
        }

        if ($semester) {
            $rekap = RekapR2Akhir::calculateAndSave($siswa, $semester, $kelas);

            return [
                'nilai_per_indikator' => $nilaiPerIndikator,
                'r2_penilaian' => $rekap->r2_penilaian,
                'r2_harian' => $rekap->r2_harian,
                'r2_akhir' => $rekap->r2_akhir,
                'jumlah_indikator' => $rekap->jumlah_indikator,
                'jumlah_terisi' => $rekap->jumlah_terisi,
                'is_mutasi' => $rekap->is_mutasi,
                'tanggal_masuk_kelas_tartil' => $siswa->tanggal_masuk_kelas_tartil,
            ];
        }

        // Fallback langka: penilaian tanpa semester — hitung manual tanpa cache
        $nilaiFilled = collect($nilaiPerIndikator)->whereNotNull('nilai')->pluck('nilai');
        $r2Penilaian = $nilaiFilled->count() > 0 ? round($nilaiFilled->avg()) : 0;
        $r2Harian = 0;
        // Nilai rapor murni dari Penilaian Internal
        $r2Akhir = $r2Penilaian;

        return [
            'nilai_per_indikator' => $nilaiPerIndikator,
            'r2_penilaian' => $r2Penilaian,
            'r2_harian' => $r2Harian,
            'r2_akhir' => $r2Akhir,
            'jumlah_indikator' => $indikators->count(),
            'jumlah_terisi' => $nilaiFilled->count(),
            'is_mutasi' => $siswa->isMutasi,
            'tanggal_masuk_kelas_tartil' => $siswa->tanggal_masuk_kelas_tartil,
        ];
    }
}
