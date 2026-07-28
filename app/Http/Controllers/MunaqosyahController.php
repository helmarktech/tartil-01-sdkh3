<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UjianMunaqosyah;
use App\Models\MunaqosyahPendaftaran;
use App\Models\MunaqosyahApproval;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MunaqosyahController extends Controller
{
    // ==================== ADMIN: INDEX (LIST UJIAN SAJA) ====================
    public function adminIndex()
    {
        $ujians = UjianMunaqosyah::with(['semester', 'pengaju', 'approver'])
            ->withCount('pendaftarans')
            ->orderBy('created_at', 'desc')->paginate(20);
        $semester = Semester::aktif()->first();
        return view('admin.munaqosyah.index', compact('ujians', 'semester'));
    }

    // ==================== ADMIN: DAFTAR SISWA (3-STEP) ====================
    // Alur: Pilih Ujian → Pilih Kelas Tartil → Checkbox Siswa → Submit
    public function adminDaftar(Request $request)
    {
        $step = 1;
        $ujianTerpilih = null;
        $kelasTerpilih = null;
        $siswaList = collect();

        // List ujian yang disetujui
        $ujianList = UjianMunaqosyah::whereIn('status', ['disetujui', 'sedang_berlangsung'])
            ->with('semester')
            ->orderBy('tanggal_ujian', 'desc')
            ->get();

        // List kelas tartil
        $kelasTartilList = Kelas::where('status', 'aktif')
            ->with('guru')
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        // Step 2: Ujian sudah dipilih, pilih kelas
        if ($request->filled('ujian_id') && $request->filled('kelas_tartil_id')) {
            $ujianTerpilih = UjianMunaqosyah::find($request->ujian_id);
            $kelasTerpilih = Kelas::find($request->kelas_tartil_id);
            if ($ujianTerpilih && $kelasTerpilih) {
                // Ambil semua siswa dari kelas, dengan status pendaftaran
                $sudahTerdaftar = MunaqosyahPendaftaran::where('munaqosyah_id', $ujianTerpilih->id)
                    ->pluck('siswa_id')
                    ->toArray();
                $siswaList = Siswa::where('kelas_tartil_id', $kelasTerpilih->id)
                    ->where('status', 'aktif')
                    ->with('kelasReguler')
                    ->orderBy('nama')
                    ->get()
                    ->map(function($s) use ($sudahTerdaftar) {
                        $s->sudah_terdaftar = in_array($s->id, $sudahTerdaftar);
                        return $s;
                    });
                $step = 2;
            }
        }
        // Step 1: Pilih ujian
        elseif ($request->filled('ujian_id')) {
            $ujianTerpilih = UjianMunaqosyah::find($request->ujian_id);
            $step = 1;
        }

        return view('admin.munaqosyah.daftar', compact(
            'ujianList', 'kelasTartilList', 'ujianTerpilih', 'kelasTerpilih', 'siswaList', 'step'
        ));
    }

    // ==================== ADMIN: SIMPAN DAFTAR SISWA KE UJIAN ====================
    public function adminDaftarSimpan(Request $request)
    {
        $request->validate([
            'ujian_id' => 'required|exists:ujian_munaqosyahs,id',
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
        ]);

        $ujian = UjianMunaqosyah::find($request->ujian_id);
        $count = 0;
        $skipped = 0;

        foreach ($request->siswa_ids as $siswaId) {
            // Cek apakah siswa sudah pernah daftar ke ujian ini
            $existing = MunaqosyahPendaftaran::where('munaqosyah_id', $ujian->id)
                ->where('siswa_id', $siswaId)
                ->first();
            if ($existing) {
                $skipped++;
                continue;
            }

            // Langsung buat status T (Terdaftar) karena admin tidak perlu approval
            $pendaftaran = MunaqosyahPendaftaran::create([
                'munaqosyah_id' => $ujian->id,
                'siswa_id' => $siswaId,
                'diajukan_oleh' => auth()->id(),
                'pengaju_type' => 'admin',
                'status' => MunaqosyahPendaftaran::STATUS_TERDAFTAR, // T
                'nilai' => null,
                'catatan' => null,
            ]);
            $count++;
        }

        $message = "{$count} siswa berhasil didaftarkan ke ujian '{$ujian->nama}'.";
        if ($skipped > 0) {
            $message .= " {$skipped} siswa dilewati karena sudah terdaftar.";
        }

        return redirect()->route('admin.munaqosyah.daftar')
            ->with('success', $message);
    }

    public function adminDetail(UjianMunaqosyah $munaqosyah)
    {
        $munaqosyah->loadCount([
            'pendaftarans as total_pendaftar' => fn($q) => $q->where('status', '!=', 'pending'),
            'pendaftarans as total_pending' => fn($q) => $q->where('status', 'pending'),
            'pendaftarans as total_lulus' => fn($q) => $q->where('status', MunaqosyahPendaftaran::STATUS_LULUS), // L
            'pendaftarans as total_tidak_lulus' => fn($q) => $q->where('status', MunaqosyahPendaftaran::STATUS_TIDAK_LULUS), // TL
            'pendaftarans as total_menunggu' => fn($q) => $q->where('status', MunaqosyahPendaftaran::STATUS_TERDAFTAR), // T
        ]);

        return view('admin.munaqosyah.detail', compact('munaqosyah'));
    }

    /**
     * Export daftar peserta munaqosyah ke Excel.
     * Format: No, Nama Siswa, Jenis Kelamin (L/P), NIS, TTL, Nama Ayah
     */
    public function adminExportPesertaExcel(UjianMunaqosyah $munaqosyah)
    {
        $peserta = $munaqosyah->pendaftarans()
            ->with('siswa')
            ->where('status', '!=', 'pending')
            ->orderByRaw("FIELD(status, 'T', 'L', 'TL')")
            ->orderBy('created_at')
            ->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Peserta ' . $munaqosyah->nama);

        // Judul
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'DAFTAR PESERTA UJIAN MUNAQOSYAH');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', $munaqosyah->nama . ' - ' . ucfirst($munaqosyah->tingkat) . ' - ' . ($munaqosyah->tanggal_ujian ? date('d F Y', strtotime($munaqosyah->tanggal_ujian)) : '-'));
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

        // Header
        $headers = ['No', 'Nama Siswa', 'Jenis Kelamin (L/P)', 'NIS', 'TTL', 'Nama Ayah'];
        $col = 1;
        foreach ($headers as $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
            $cellRef = $colLetter . '4';
            $sheet->setCellValue($cellRef, $header);
            $sheet->getStyle($cellRef)->getFont()->setBold(true);
            $sheet->getStyle($cellRef)->getAlignment()->setHorizontal('center');
            $sheet->getStyle($cellRef)->getFill()->setFillType('solid')->getStartColor()->setRGB('F5F5F4');
            $sheet->getStyle($cellRef)->getBorders()->getAllBorders()->setBorderStyle('thin');
            $col++;
        }

        // Data
        $bulanIndo = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        $row = 5;
        foreach ($peserta as $i => $p) {
            $s = $p->siswa;
            if (!$s) continue;

            // TTL: Surabaya, 12 April 2008
            $ttl = '-';
            if ($s->tempat_lahir && $s->tanggal_lahir) {
                $tgl = $s->tanggal_lahir instanceof \Carbon\Carbon ? $s->tanggal_lahir : \Carbon\Carbon::parse($s->tanggal_lahir);
                $ttl = $s->tempat_lahir . ', ' . $tgl->day . ' ' . $bulanIndo[$tgl->month] . ' ' . $tgl->year;
            } elseif ($s->tanggal_lahir) {
                $tgl = $s->tanggal_lahir instanceof \Carbon\Carbon ? $s->tanggal_lahir : \Carbon\Carbon::parse($s->tanggal_lahir);
                $ttl = $tgl->day . ' ' . $bulanIndo[$tgl->month] . ' ' . $tgl->year;
            } elseif ($s->tempat_lahir) {
                $ttl = $s->tempat_lahir;
            }

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $s->nama);
            $sheet->setCellValue('C' . $row, $s->jenis_kelamin === 'P' ? 'P' : 'L');
            $sheet->setCellValue('D' . $row, $s->nis);
            $sheet->setCellValue('E' . $row, $ttl);
            $sheet->setCellValue('F' . $row, $s->nama_ayah ?? '-');

            // Border
            foreach (range('A', 'F') as $c) {
                $sheet->getCell($c . $row)->getStyle()->getBorders()->getAllBorders()->setBorderStyle('thin');
            }

            $row++;
        }

        // Lebar kolom
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(32);
        $sheet->getColumnDimension('F')->setWidth(28);

        $filename = 'Peserta_Munaqosyah_' . preg_replace('/[^A-Za-z0-9]/', '_', $munaqosyah->nama) . '.xlsx';

        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }

    public function adminStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:100',
            'tingkat' => 'required|in:unit,yayasan,pesantren',
            'tanggal_ujian' => 'required|date',
            'semester_id' => 'required|exists:semesters,id',
        ]);

        // Admin buat ujian langsung disetujui
        $ujian = UjianMunaqosyah::create([
            'nama' => $request->nama,
            'tingkat' => $request->tingkat,
            'tanggal_ujian' => $request->tanggal_ujian,
            'semester_id' => $request->semester_id,
            'status' => 'disetujui',
            'diajukan_oleh' => auth()->id(),
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.munaqosyah.index')
            ->with('success', "Ujian '{$ujian->nama}' berhasil dibuat dan disetujui. Silakan daftarkan siswa melalui menu Daftar Siswa.");
    }

    public function adminApprove(UjianMunaqosyah $munaqosyah)
    {
        $munaqosyah->update([
            'status' => 'disetujui',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Ujian munaqosyah disetujui.');
    }

    public function adminTolak(Request $request, UjianMunaqosyah $munaqosyah)
    {
        $munaqosyah->update([
            'status' => 'ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'catatan' => $request->catatan,
        ]);
        return back()->with('success', 'Ujian munaqosyah ditolak.');
    }

    // ==================== ADMIN: BUKA / TUTUP PENDAFTARAN ====================
    public function adminBukaPendaftaran(UjianMunaqosyah $munaqosyah)
    {
        $munaqosyah->bukaPendaftaran();
        return back()->with('success', "Pendaftaran ujian '{$munaqosyah->nama}' dibuka. Siswa sekarang bisa mendaftar.");
    }

    public function adminTutupPendaftaran(UjianMunaqosyah $munaqosyah)
    {
        $munaqosyah->tutupPendaftaran();

        $totalPeserta = $munaqosyah->pendaftarans()->count();
        $totalLulus = $munaqosyah->jumlahLulus();
        $totalTidakLulus = $munaqosyah->jumlahTidakLulus();

        return back()->with('success',
            "Pendaftaran ujian '{$munaqosyah->nama}' ditutup. " .
            "Rekap: {$totalPeserta} peserta, {$totalLulus} lulus, {$totalTidakLulus} tidak lulus."
        );
    }

    // ==================== ADMIN: DAFTARKAN SISWA (langsung T) ====================
    public function adminDaftarkan(Request $request, UjianMunaqosyah $munaqosyah)
    {
        if ($munaqosyah->isPendaftaranTutup()) {
            return back()->with('error', "Pendaftaran ujian '{$munaqosyah->nama}' sudah ditutup. Admin harus membuka kembali untuk mendaftarkan siswa.");
        }

        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
        ]);
        $count = 0;
        foreach ($request->siswa_ids as $siswaId) {
            $pendaftaran = MunaqosyahPendaftaran::firstOrCreate(
                ['munaqosyah_id' => $munaqosyah->id, 'siswa_id' => $siswaId],
                [
                    'status' => MunaqosyahPendaftaran::STATUS_TERDAFTAR,
                    'diajukan_oleh' => auth()->id(),
                    'pengaju_type' => 'admin',
                ]
            );
            if ($pendaftaran->wasRecentlyCreated) $count++;
        }
        return back()->with('success', $count . ' siswa berhasil didaftarkan.');
    }

    // ==================== GURU: APPROVAL REKAP ====================
    public function guruApprovalRekap()
    {
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        $kelasIds = Kelas::where('guru_id', $guru->id)->pluck('id');

        $pendaftarans = MunaqosyahPendaftaran::with(['munaqosyah', 'siswa', 'pengaju', 'approval'])
            ->whereHas('siswa', function ($q) use ($kelasIds) {
                $q->whereIn('kelas_tartil_id', $kelasIds);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('guru.munaqosyah.approval-rekap', compact('pendaftarans', 'guru'));
    }

    // ==================== GURU: INDEX ====================
    public function guruIndex()
    {
        // Guru hanya lihat ujian yang sudah disetujui
        $ujians = UjianMunaqosyah::with(['semester'])
            ->where('status', 'disetujui')
            ->orderBy('created_at', 'desc')->paginate(20);
        $semester = Semester::aktif()->first();
        return view('guru.munaqosyah.index', compact('ujians', 'semester'));
    }

    // ==================== GURU: DAFTARKAN SISWA (langsung T, tanpa approval) ====================
    public function guruDaftarkan(Request $request, UjianMunaqosyah $munaqosyah)
    {
        if ($munaqosyah->isPendaftaranTutup()) {
            return back()->with('error', "Pendaftaran ujian '{$munaqosyah->nama}' sudah ditutup. Hubungi admin untuk membuka kembali.");
        }

        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
        ]);
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        $kelasIds = Kelas::where('guru_id', $guru->id)->pluck('id');
        $count = 0;

        foreach ($request->siswa_ids as $siswaId) {
            $siswa = Siswa::find($siswaId);
            if (!$kelasIds->contains($siswa->kelas_tartil_id)) {
                return back()->with('error', "Siswa {$siswa->nama} tidak ada di kelas Anda.");
            }
            // Cek apakah siswa sudah pernah daftar ke ujian ini (apapun statusnya)
            $existing = MunaqosyahPendaftaran::where('munaqosyah_id', $munaqosyah->id)
                ->where('siswa_id', $siswaId)
                ->first();
            if ($existing) continue; // skip kalau sudah pernah daftar

            // Buat pendaftaran status T + approval record pending
            $pendaftaran = MunaqosyahPendaftaran::create([
                'munaqosyah_id' => $munaqosyah->id,
                'siswa_id' => $siswaId,
                'diajukan_oleh' => auth()->id(),
                'pengaju_type' => 'guru',
                'status' => MunaqosyahPendaftaran::STATUS_TERDAFTAR,
            ]);

            // Buat approval record pending — perlu admin approve baru bisa input nilai
            \App\Models\MunaqosyahApproval::create([
                'pendaftaran_id' => $pendaftaran->id,
                'status' => 'pending',
            ]);
            $count++;
        }
        return back()->with('success', $count . ' siswa berhasil didaftarkan.');
    }

    public function guruDetail(UjianMunaqosyah $munaqosyah)
    {
        $munaqosyah->load(['pendaftarans.siswa', 'pendaftarans.approval', 'semester']);
        // Guru hanya bisa lihat siswa dari kelasnya sendiri untuk didaftarkan
        $guru = auth()->user()?->guru;
        if (!$guru) return back()->with('error', 'Data guru tidak ditemukan untuk akun ini. Hubungi admin.');
        $kelasIds = Kelas::where('guru_id', $guru->id)->pluck('id');
        $siswas = Siswa::whereIn('kelas_tartil_id', $kelasIds)
            ->where('status', 'aktif')
            ->whereNotIn('id', $munaqosyah->pendaftarans->pluck('siswa_id'))
            ->orderBy('nama')->get();
        return view('guru.munaqosyah.detail', compact('munaqosyah', 'siswas'));
    }

    // ==================== GURU: INPUT NILAI (BATCH) ====================
    // Validasi: hanya siswa yang sudah di-approve admin yang bisa dinilai
    public function guruNilaiBatch(Request $request, UjianMunaqosyah $munaqosyah)
    {
        $request->validate([
            'nilai' => 'required|array',
            'nilai.*.pendaftaran_id' => 'required|exists:munaqosyah_pendaftarans,id',
            'nilai.*.status' => 'required|in:T,L,TL',
            'nilai.*.nilai' => 'nullable|integer|min:0|max:100',
            'nilai.*.catatan' => 'nullable',
        ]);
        DB::beginTransaction();
        try {
            foreach ($request->nilai as $n) {
                $pendaftaran = MunaqosyahPendaftaran::with('approval')->find($n['pendaftaran_id']);
                if ($pendaftaran->munaqosyah_id !== $munaqosyah->id) continue;

                // Validasi: hanya siswa yang sudah di-approve admin yang bisa dinilai
                if ($pendaftaran->approval && $pendaftaran->approval->status === 'pending') {
                    throw new \Exception('Siswa ' . ($pendaftaran->siswa->nama ?? '') . ' belum di-approve oleh admin. Tidak dapat menginput nilai.');
                }

                $pendaftaran->update([
                    'status' => $n['status'],
                    'nilai' => $n['nilai'] ?? null,
                    'catatan' => $n['catatan'] ?? null,
                ]);
            }
            DB::commit();
            return back()->with('success', 'Nilai berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // ==================== ADMIN: INPUT NILAI (BATCH) ====================
    // Admin juga bisa input nilai jika guru berhalangan
    public function adminNilaiBatch(Request $request, UjianMunaqosyah $munaqosyah)
    {
        $request->validate([
            'nilai' => 'required|array',
            'nilai.*.pendaftaran_id' => 'required|exists:munaqosyah_pendaftarans,id',
            'nilai.*.status' => 'required|in:T,L,TL',
            'nilai.*.nilai' => 'nullable|integer|min:0|max:100',
            'nilai.*.catatan' => 'nullable',
        ]);
        DB::beginTransaction();
        try {
            foreach ($request->nilai as $n) {
                $pendaftaran = MunaqosyahPendaftaran::with('approval')->find($n['pendaftaran_id']);
                if ($pendaftaran->munaqosyah_id !== $munaqosyah->id) continue;

                // Admin juga harus approve dulu kalau masih pending
                if ($pendaftaran->approval && $pendaftaran->approval->status === 'pending') {
                    throw new \Exception('Siswa ' . ($pendaftaran->siswa->nama ?? '') . ' belum di-approve. Approve terlebih dahulu di menu Approval Pendaftaran.');
                }

                $pendaftaran->update([
                    'status' => $n['status'],
                    'nilai' => $n['nilai'] ?? null,
                    'catatan' => $n['catatan'] ?? null,
                ]);
            }
            DB::commit();
            return back()->with('success', 'Nilai munaqosyah berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    // ==================== ADMIN: BATAL PENDAFTARAN ====================
    public function adminBatalPendaftaran(Request $request, UjianMunaqosyah $munaqosyah, MunaqosyahPendaftaran $pendaftaran)
    {
        // Validasi: pendaftaran harus milik munaqosyah ini
        if ($pendaftaran->munaqosyah_id !== $munaqosyah->id) {
            return back()->with('error', 'Data pendaftaran tidak valid.');
        }

        $namaSiswa = $pendaftaran->siswa->nama ?? 'Siswa';

        DB::beginTransaction();
        try {
            // Hapus approval terkait kalau ada
            $pendaftaran->approval()->delete();

            // Hapus pendaftaran
            $pendaftaran->delete();

            DB::commit();
            return back()->with('success', "Pendaftaran '{$namaSiswa}' berhasil dibatalkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membatalkan pendaftaran: ' . $e->getMessage());
        }
    }

    // ==================== BATCH: LULUS SEMUA / TIDAK LULUS SEMUA ====================
    // Hanya siswa yang sudah di-approve admin yang bisa diubah statusnya
    public function guruLulusSemua(UjianMunaqosyah $munaqosyah)
    {
        $count = $munaqosyah->pendaftarans()
            ->where('status', MunaqosyahPendaftaran::STATUS_TERDAFTAR)
            ->whereDoesntHave('approval', fn($q) => $q->where('status', 'pending'))
            ->update([
                'status' => MunaqosyahPendaftaran::STATUS_LULUS,
            ]);
        return back()->with('success', "{$count} siswa dinyatakan lulus.");
    }

    public function guruTidakLulusSemua(UjianMunaqosyah $munaqosyah)
    {
        $count = $munaqosyah->pendaftarans()
            ->where('status', MunaqosyahPendaftaran::STATUS_TERDAFTAR)
            ->whereDoesntHave('approval', fn($q) => $q->where('status', 'pending'))
            ->update([
                'status' => MunaqosyahPendaftaran::STATUS_TIDAK_LULUS,
            ]);
        return back()->with('success', "{$count} siswa dinyatakan tidak lulus.");
    }

    // ==================== ADMIN: REKAP HISTORY SEKOLAH ====================
    public function adminRekapHistory(Request $request)
    {
        // Statistik keseluruhan
        $totalUjian = UjianMunaqosyah::count();
        $totalPeserta = MunaqosyahPendaftaran::count();
        $totalLulus = MunaqosyahPendaftaran::where('status', MunaqosyahPendaftaran::STATUS_LULUS)->count();
        $totalTidakLulus = MunaqosyahPendaftaran::where('status', MunaqosyahPendaftaran::STATUS_TIDAK_LULUS)->count();
        $totalTerdaftar = MunaqosyahPendaftaran::where('status', MunaqosyahPendaftaran::STATUS_TERDAFTAR)->count();
        $persentaseKelulusan = $totalPeserta > 0 ? round(($totalLulus / $totalPeserta) * 100, 1) : 0;

        // Daftar semua ujian dengan statistik
        $ujians = UjianMunaqosyah::with(['semester'])
            ->withCount([
                'pendaftarans as total_peserta',
                'pendaftarans as total_lulus' => fn($q) => $q->where('status', MunaqosyahPendaftaran::STATUS_LULUS),
                'pendaftarans as total_tidak_lulus' => fn($q) => $q->where('status', MunaqosyahPendaftaran::STATUS_TIDAK_LULUS),
                'pendaftarans as total_terdaftar' => fn($q) => $q->where('status', MunaqosyahPendaftaran::STATUS_TERDAFTAR),
            ])
            ->orderBy('tanggal_ujian', 'desc')
            ->paginate(20);

        // Top 10 siswa yang paling sering lulus
        $topLulus = MunaqosyahPendaftaran::select('siswa_id')
            ->selectRaw('COUNT(*) as jumlah_lulus')
            ->where('status', MunaqosyahPendaftaran::STATUS_LULUS)
            ->groupBy('siswa_id')
            ->orderByDesc('jumlah_lulus')
            ->limit(10)
            ->with('siswa')
            ->get();

        return view('admin.munaqosyah.rekap', compact(
            'totalUjian', 'totalPeserta', 'totalLulus', 'totalTidakLulus',
            'totalTerdaftar', 'persentaseKelulusan', 'ujians', 'topLulus'
        ));
    }

    // ==================== ADMIN: REKAP HISTORY PER SISWA ====================
    public function adminRekapPerSiswa(Request $request, Siswa $siswa)
    {
        $riwayat = MunaqosyahPendaftaran::with(['munaqosyah.semester'])
            ->where('siswa_id', $siswa->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $statistik = [
            'total_ikut' => MunaqosyahPendaftaran::where('siswa_id', $siswa->id)->count(),
            'total_lulus' => MunaqosyahPendaftaran::where('siswa_id', $siswa->id)->where('status', MunaqosyahPendaftaran::STATUS_LULUS)->count(),
            'total_tidak_lulus' => MunaqosyahPendaftaran::where('siswa_id', $siswa->id)->where('status', MunaqosyahPendaftaran::STATUS_TIDAK_LULUS)->count(),
            'total_terdaftar' => MunaqosyahPendaftaran::where('siswa_id', $siswa->id)->where('status', MunaqosyahPendaftaran::STATUS_TERDAFTAR)->count(),
        ];
        $statistik['persentase_kelulusan'] = $statistik['total_ikut'] > 0
            ? round(($statistik['total_lulus'] / $statistik['total_ikut']) * 100, 1)
            : 0;

        return view('admin.munaqosyah.rekap-siswa', compact('siswa', 'riwayat', 'statistik'));
    }

    // ==================== SISWA: LIHAT RIWAYAT ====================
    public function siswaIndex()
    {
        $siswa = auth('siswa')->user();
        $riwayat = MunaqosyahPendaftaran::with(['munaqosyah.semester'])
            ->where('siswa_id', $siswa->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Hitung statistik untuk siswa
        $statistik = [
            'total_ikut' => MunaqosyahPendaftaran::where('siswa_id', $siswa->id)->count(),
            'total_lulus' => MunaqosyahPendaftaran::where('siswa_id', $siswa->id)->where('status', MunaqosyahPendaftaran::STATUS_LULUS)->count(),
            'total_tidak_lulus' => MunaqosyahPendaftaran::where('siswa_id', $siswa->id)->where('status', MunaqosyahPendaftaran::STATUS_TIDAK_LULUS)->count(),
            'total_terdaftar' => MunaqosyahPendaftaran::where('siswa_id', $siswa->id)->where('status', MunaqosyahPendaftaran::STATUS_TERDAFTAR)->count(),
        ];
        $statistik['persentase_kelulusan'] = $statistik['total_ikut'] > 0
            ? round(($statistik['total_lulus'] / $statistik['total_ikut']) * 100, 1)
            : 0;

        return view('siswa.munaqosyah', compact('riwayat', 'statistik'));
    }
}
