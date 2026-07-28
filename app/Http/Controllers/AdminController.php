<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Guru;
use App\Models\GuruTartil;
use App\Models\GuruReguler;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\Semester;
use App\Models\PerpindahanKelas;
use App\Models\MunaqosyahPendaftaran;
use App\Models\MunaqosyahApproval;
use App\Models\User;
use App\Models\KenaikanKelasReguler;
use App\Models\TahunAjaran;
use App\Models\SemesterSiswa;
use App\Models\SemesterKelas;
use App\Models\RiwayatMutasi;
use App\Models\UjianMunaqosyah;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\KopSuratRapor;
use App\Models\SemesterAuditLog;
use App\Models\RekapJurnalSemester;
use App\Models\RekapMunaqosyahSemester;
use App\Models\RekapRiwayatSemester;
use App\Models\RekapTahfidzSemester;
use App\Models\RekapR2Akhir;
use App\Models\IndikatorPenilaian;
use App\Models\PenilaianRaporInternal;
use App\Models\PenilaianRaporNilai;
use App\Models\JurnalHarian;

class AdminController extends Controller
{
    // ============ DASHBOARD ============
    public function dashboard()
    {
        $stats = [
            'total_guru' => Guru::where('is_aktif', true)->count(),
            'total_siswa' => Siswa::where('status', 'aktif')->count(),
            'total_kelas' => Kelas::where('status', 'aktif')->count(),
            'pending_pindah' => PerpindahanKelas::where('status', 'pending')->count(),
        ];

        // Rekap siswa per jenis kelas (BQ 1, BQ 2, BQ 3, BQ 4, Tartil, Tahfidz)
        // Fix: only_full_group_by — pisah query count siswa dari groupBy jenis
        $kelasAktif = Kelas::where('status', 'aktif')
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->get();

        $rekapBQ = $kelasAktif->groupBy('jenis')
            ->sortBy(fn($group, $jenis) => array_search($jenis, ['BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz']) ?: 99)
            ->mapWithKeys(fn($group, $jenis) => [$jenis => [
                'kelas' => $group->count(),
                'siswa' => $group->sum('siswas_count'),
            ]]);

        // Penyebaran siswa per kelas tartil (detail)
        $penyebaranKelas = Kelas::where('status', 'aktif')
            ->with(['guru'])
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        $recentPerpindahan = PerpindahanKelas::with(['siswa', 'kelasLama', 'kelasBaru'])
            ->orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'rekapBQ', 'penyebaranKelas', 'recentPerpindahan'));
    }

    // ============ GURU MANAGEMENT ============
    public function guruIndex()
    {
        $gurus = Guru::orderBy('nama')->paginate(20);
        return view('admin.guru.index', compact('gurus'));
    }

    public function guruCreate()
    {
        return view('admin.guru.create');
    }

    public function guruStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100',
            'nip' => 'nullable|unique:guru_tartils,nip',
            'email' => 'required|email|unique:guru_tartils,email',
            'no_hp' => 'required|max:15',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable',
        ]);

        $guru = Guru::create($validated);

        // Buat user login untuk guru
        User::create([
            'nama' => $guru->nama,
            'email' => $guru->email,
            'password' => Hash::make('guru123'),
            'role' => 'guru',
            'guru_id' => $guru->id,
        ]);

        return redirect()->route('admin.guru.index')->with('success', 'Guru berhasil ditambahkan. Password default: guru123');
    }

    public function guruEdit(Guru $guru)
    {
        return view('admin.guru.edit', compact('guru'));
    }

    public function guruUpdate(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100',
            'nip' => 'nullable|unique:guru_tartils,nip,' . $guru->id,
            'email' => 'required|email|unique:guru_tartils,email,' . $guru->id,
            'no_hp' => 'required|max:15',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable',
            'is_aktif' => 'boolean',
        ]);

        try {
            $guru->update($validated);

            // Sinkronkan nama user login jika berubah
            if (isset($validated['nama']) && $guru->user) {
                $guru->user->update(['nama' => $validated['nama']]);
            }
            // Sinkronkan email user login jika berubah
            if (isset($validated['email']) && $guru->user && $guru->user->email !== $validated['email']) {
                $guru->user->update(['email' => $validated['email']]);
            }

            return redirect()->route('admin.guru.index')->with('success', 'Data guru tartil "' . $validated['nama'] . '" berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    // ============ SISWA MANAGEMENT ============
    public function siswaIndex(Request $request)
    {
        $query = Siswa::with(['kelasReguler', 'kelasTartil']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('kelas_reguler')) {
            $query->where('kelas_reguler_id', $request->kelas_reguler);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $siswas = $query->orderBy('nama')->paginate(20);
        $kelasRegulars = KelasReguler::where('is_aktif', true)->orderBy('nama')->get();

        return view('admin.siswa.index', compact('siswas', 'kelasRegulars'));
    }

    public function siswaCreate()
    {
        $kelasRegulars = KelasReguler::where('is_aktif', true)->get();
        $kelasTartils = Kelas::where('status', 'aktif')->get();
        return view('admin.siswa.create', compact('kelasRegulars', 'kelasTartils'));
    }

    public function siswaStore(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|unique:siswas,nis',
            'nama' => 'required|max:100',
            'no_hp' => 'required|max:15',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_reguler_id' => 'required|exists:kelas_regulers,id',
            'kelas_tartil_id' => 'nullable|exists:kelas,id',
            'tanggal_masuk_kelas_tartil' => 'nullable|date',
            'keterangan_mutasi' => 'nullable|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable',
            'nama_ayah' => 'nullable',
            'no_hp_ortu' => 'nullable',
            'tanggal_masuk' => 'required|date',
        ]);

        $validated['password'] = Hash::make($request->nis); // default password = NIS
        Siswa::create($validated);

        return redirect()->route('admin.siswa.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function siswaShow(Siswa $siswa)
    {
        $siswa->load(['kelasReguler', 'kelasTartil', 'perpindahanKelas.kelasLama', 'perpindahanKelas.kelasBaru', 'perpindahanKelas.semester']);
        return view('admin.siswa.show', compact('siswa'));
    }

    public function siswaEdit(Siswa $siswa)
    {
        $siswa->load(['kelasReguler', 'kelasTartil']);
        $kelasRegulars = KelasReguler::where('is_aktif', true)->orderBy('nama')->get();
        $kelasTartils = Kelas::where('status', 'aktif')->orderBy('nama')->get();
        return view('admin.siswa.edit', compact('siswa', 'kelasRegulars', 'kelasTartils'));
    }

    public function siswaUpdate(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nis' => 'required|unique:siswas,nis,' . $siswa->id,
            'nama' => 'required|max:100',
            'no_hp' => 'required|max:15',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_reguler_id' => 'required|exists:kelas_regulers,id',
            'kelas_tartil_id' => 'nullable|exists:kelas,id',
            'tanggal_masuk_kelas_tartil' => 'nullable|date',
            'keterangan_mutasi' => 'nullable|max:255',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable',
            'nama_ayah' => 'nullable',
            'no_hp_ortu' => 'nullable',
            'tanggal_masuk' => 'required|date',
            'status' => 'required|in:aktif,mutasi_keluar,lulus,nonaktif',
            'password' => 'nullable|min:4',
        ]);

        // Update password hanya jika diisi
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $siswa->update($validated);

        return redirect()->route('admin.siswa.show', $siswa)->with('success', 'Data siswa berhasil diperbarui.');
    }

    // ============ KELAS MANAGEMENT ============
    public function kelasIndex()
    {
        $kelas = Kelas::with('guru')
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->orderBy('nama')
            ->paginate(20);
        return view('admin.kelas.index', compact('kelas'));
    }

    public function kelasTartilIndex()
    {
        $kelas = Kelas::with(['guru'])->withCount(['siswas' => function($q) {
            $q->where('status', 'aktif');
        }])->orderBy('nama')->paginate(20);
        return view('admin.kelas-tartil.index', compact('kelas'));
    }

    public function kelasCreate()
    {
        $gurus = Guru::where('is_aktif', true)->orderBy('nama')->get();
        return view('admin.kelas.create', compact('gurus'));
    }

    public function kelasStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100',
            'jenis' => 'required|in:BQ 1,BQ 2,BQ 3,BQ 4,Tartil,Tahfidz',
            'guru_id' => 'nullable|exists:guru_tartils,id',
            'deskripsi' => 'nullable|string',
            'tanggal_dibuat' => 'nullable|date',
        ]);

        $validated['mata_pelajaran'] = $validated['jenis'];

        Kelas::create($validated);
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas ' . $validated['nama'] . ' (' . $validated['jenis'] . ') berhasil dibuat.');
    }

    public function kelasEdit(Kelas $kelas)
    {
        $gurus = Guru::where('is_aktif', true)->orderBy('nama')->get();
        return view('admin.kelas.edit', compact('kelas', 'gurus'));
    }

    public function kelasUpdate(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100',
            'jenis' => 'required|in:BQ 1,BQ 2,BQ 3,BQ 4,Tartil,Tahfidz',
            'guru_id' => 'nullable|exists:guru_tartils,id',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif',
            'tanggal_dibuat' => 'nullable|date',
        ]);

        $kelas->update($validated);
        return redirect()->route('admin.kelas.index')->with('success', 'Data kelas ' . $validated['nama'] . ' (' . $validated['jenis'] . ') diperbarui.');
    }

    // ============ SEMESTER MANAGEMENT (COMPREHENSIVE) ============
    public function semesterIndex()
    {
        $semesters = Semester::with(['tahunAjaran'])->withCount(['kelasTartils', 'siswas'])
            ->orderBy('tanggal_mulai', 'desc')
            ->orderBy('jenis')
            ->paginate(20);
        return view('admin.semester.index', compact('semesters'));
    }

    private function semesterMigrasi(Semester $semesterBaru, bool $migrasi = false)
    {
        if (!$migrasi) return;

        // Cari semester sebelumnya
        $semesterLama = Semester::where('id', '!=', $semesterBaru->id)
            ->orderBy('tanggal_mulai', 'desc')
            ->first();

        if (!$semesterLama) return;

        // Migrasi kelas tartil
        $kelasIds = $semesterLama->kelasTartils->pluck('id');
        if ($kelasIds->isNotEmpty()) {
            $pivotData = [];
            foreach ($semesterLama->kelasTartils as $k) {
                $pivotData[$k->id] = [
                    'jumlah_siswa' => $k->pivot->jumlah_siswa,
                    'keterangan' => 'Dimigrasi dari ' . $semesterLama->nama,
                ];
            }
            $semesterBaru->kelasTartils()->sync($pivotData);
        }

        // Migrasi siswa aktif dengan snapshot kelas reguler & tartil saat ini
        $siswaIds = $semesterLama->siswas()
            ->wherePivotIn('status_siswa', ['aktif', 'pindah'])
            ->get();
        if ($siswaIds->isNotEmpty()) {
            $pivotData = [];
            foreach ($siswaIds as $s) {
                // Ambil kelas reguler & tartil terbaru dari tabel siswas
                $siswaFresh = \App\Models\Siswa::find($s->id);
                $pivotData[$s->id] = [
                    'kelas_id' => $siswaFresh->kelas_tartil_id,
                    'kelas_reguler_id' => $siswaFresh->kelas_reguler_id,
                    'status_siswa' => 'aktif',
                    'keterangan' => 'Dimigrasi dari ' . $semesterLama->nama,
                ];
            }
            $semesterBaru->siswas()->sync($pivotData);
        }
    }

    public function semesterDetail(Semester $semester)
    {
        $semester->load(['tahunAjaran', 'kelasTartils.guru', 'siswas.kelasReguler', 'siswas.kelasTartil']);

        // Load snapshot dari semester_kelas dan semester_siswa juga
        $snapshotKelas = SemesterKelas::where('semester_id', $semester->id)
            ->with('kelas.guru')
            ->get();
        $snapshotSiswa = SemesterSiswa::where('semester_id', $semester->id)
            ->with(['siswa.kelasReguler', 'siswa.kelasTartil', 'kelasTartil'])
            ->get();

        return view('admin.semester.detail', compact('semester', 'snapshotKelas', 'snapshotSiswa'));
    }

    public function semesterAktifkan(Semester $semester)
    {
        if ($semester->status == 'ditutup') {
            return back()->with('error', 'Semester sudah ditutup dan tidak dapat diaktifkan.');
        }

        // Nonaktifkan semester lain yang sedang aktif
        Semester::where('is_aktif', true)->update(['is_aktif' => false, 'status' => 'nonaktif']);
        $semester->update(['is_aktif' => true, 'status' => 'aktif']);

        // === SNAPSHOT DATA KE SEMESTER YANG DIAKTIFKAN ===
        // Hapus snapshot lama untuk semester ini agar tidak duplikat
        SemesterKelas::where('semester_id', $semester->id)->delete();
        SemesterSiswa::where('semester_id', $semester->id)->delete();

        // Snapshot kelas tartil aktif + jumlah siswa
        $kelasTartil = Kelas::where('status', 'aktif')->get();
        foreach ($kelasTartil as $k) {
            $jumlah = Siswa::where('kelas_tartil_id', $k->id)->where('status', 'aktif')->count();
            SemesterKelas::create([
                'semester_id' => $semester->id,
                'kelas_id' => $k->id,
                'jumlah_siswa' => $jumlah,
                'keterangan' => "Snapshot aktifkan {$semester->nama}",
            ]);
        }

        // Snapshot siswa aktif beserta kelas reguler & tartil mereka
        $siswaAktif = Siswa::where('status', 'aktif')->get();
        foreach ($siswaAktif as $s) {
            SemesterSiswa::create([
                'semester_id' => $semester->id,
                'siswa_id' => $s->id,
                'kelas_id' => $s->kelas_tartil_id,
                'kelas_reguler_id' => $s->kelas_reguler_id,
                'status_siswa' => 'aktif',
                'keterangan' => "Snapshot aktifkan {$semester->nama}",
            ]);
        }

        return back()->with('success', 'Semester ' . $semester->nama . ' diaktifkan. Data rekap: ' . $kelasTartil->count() . ' kelas, ' . $siswaAktif->count() . ' siswa.');
    }

    // ============ MUNAQOSYAH: APPROVAL PENDAFTARAN ============
    public function munaqosyahApprovalIndex()
    {
        $pendaftarans = MunaqosyahPendaftaran::with(['siswa.kelasReguler', 'siswa.kelasTartil', 'munaqosyah.semester'])
            ->whereHas('approval', fn($q) => $q->where('status', 'pending'))
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $kelasTartilIds = auth()->user()->isAdmin()
            ? null
            : Kelas::where('guru_id', auth()->user()?->guru?->id ?? 0)->pluck('id');

        return view('admin.munaqosyah.approval', compact('pendaftarans', 'kelasTartilIds'));
    }

    public function munaqosyahApprovalSetuju(MunaqosyahApproval $approval)
    {
        if ($approval->status !== 'pending') {
            return back()->with('error', 'Approval sudah diproses sebelumnya.');
        }

        return \DB::transaction(function () use ($approval) {
            $approval->update([
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Update pendaftaran status dari pending → T (Terdaftar)
            $pendaftaran = $approval->pendaftaran;
            $pendaftaran->update(['status' => MunaqosyahPendaftaran::STATUS_TERDAFTAR]);

            // Update kelas tartil siswa ke kelas ujian (kalau beda)
            $ujian = $pendaftaran->munaqosyah;
            $siswa = $pendaftaran->siswa;

            // Catat riwayat perubahan
            RiwayatMutasi::create([
                'mutasi_type' => Siswa::class,
                'mutasi_id' => $siswa->id,
                'jenis' => 'munaqosyah_approved',
                'keterangan' => "Pendaftaran ujian {$ujian->nama} ({$ujian->tingkat}) disetujui. Diajukan oleh " . ($pendaftaran->pengaju_type ?? '?') . ".",
                'dilakukan_oleh' => auth()->id(),
                'tanggal_mutasi' => now(),
            ]);

            return back()->with('success', 'Pendaftaran siswa ' . $siswa->nama . ' untuk ujian ' . $ujian->nama . ' disetujui.');
        });
    }

    public function munaqosyahApprovalTolak(Request $request, MunaqosyahApproval $approval)
    {
        $approval->update([
            'status' => 'ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'catatan' => $request->catatan,
        ]);
        return back()->with('success', 'Pendaftaran siswa ditolak.');
    }

    // ==================== APPROVAL MASSAL MUNAQOSYAH ====================
    public function munaqosyahApprovalSetujuMassal(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:munaqosyah_approvals,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $approval = MunaqosyahApproval::find($id);
            if (!$approval || $approval->status !== 'pending') continue;

            DB::transaction(function () use ($approval) {
                $approval->update([
                    'status' => 'disetujui',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
                $approval->pendaftaran()->update(['status' => MunaqosyahPendaftaran::STATUS_TERDAFTAR]);
            });
            $count++;
        }

        return back()->with('success', "{$count} pendaftaran siswa disetujui.");
    }

    public function munaqosyahApprovalTolakMassal(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:munaqosyah_approvals,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $approval = MunaqosyahApproval::find($id);
            if (!$approval || $approval->status !== 'pending') continue;

            $approval->update([
                'status' => 'ditolak',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            $count++;
        }

        return back()->with('success', "{$count} pendaftaran siswa ditolak.");
    }

    // ============ PERPINDAHAN KELAS (APPROVAL) ============
    public function perpindahanIndex()
    {
        $perpindahans = PerpindahanKelas::with(['siswa', 'kelasLama', 'kelasBaru', 'semester'])
            ->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.perpindahan.index', compact('perpindahans'));
    }

    public function perpindahanApprove(PerpindahanKelas $perpindahan)
    {
        $perpindahan->update([
            'status' => 'disetujui',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        // Update kelas siswa
        $perpindahan->siswa->update(['kelas_tartil_id' => $perpindahan->kelas_baru_id]);

        return back()->with('success', 'Perpindahan kelas disetujui.');
    }

    public function perpindahanTolak(Request $request, PerpindahanKelas $perpindahan)
    {
        $perpindahan->update([
            'status' => 'ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'catatan' => $request->catatan,
        ]);
        return back()->with('success', 'Perpindahan kelas ditolak.');
    }

    // ============ KELAS REGULER CRUD + KETERANGAN ============
    public function kelasRegulerIndex()
    {
        $kelasRegulers = KelasReguler::with('guruPengampu')
            ->orderBy('jenjang')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->paginate(20);
        $gurus = GuruReguler::where('is_aktif', true)->orderBy('nama')->get();
        return view('admin.kelas-reguler.daftar', compact('kelasRegulers', 'gurus'));
    }

    public function kelasRegulerStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:50|unique:kelas_regulers,nama',
            'jenjang' => 'required|integer|min:1|max:6',
            'tingkat' => 'required|string|max:20',
            'guru_pengampu_id' => 'nullable|exists:guru_regulers,id',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.unique' => 'Rombel :input sudah ada. Silakan gunakan nama lain.',
        ]);
        $validated['is_aktif'] = true;

        KelasReguler::create($validated);
        return redirect()->route('admin.kelas-reguler.daftar')->with('success', 'Kelas reguler "' . $validated['nama'] . '" berhasil ditambahkan.');
    }

    public function kelasRegulerUpdate(Request $request, KelasReguler $kelasReguler)
    {
        $validated = $request->validate([
            'nama' => 'required|max:50|unique:kelas_regulers,nama,' . $kelasReguler->id,
            'jenjang' => 'required|integer|min:1|max:6',
            'tingkat' => 'required|string|max:20',
            'guru_pengampu_id' => 'nullable|exists:guru_regulers,id',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.unique' => 'Rombel :input sudah ada. Silakan gunakan nama lain.',
        ]);
        $validated['is_aktif'] = $request->has('is_aktif');

        $kelasReguler->update($validated);
        return redirect()->route('admin.kelas-reguler.daftar')->with('success', 'Kelas reguler "' . $validated['nama'] . '" diperbarui.');
    }

    // Keterangan Kelas = lihat siswa per kelas reguler beserta kelas tartil & guru mereka
    public function kelasRegulerSiswa(Request $request)
    {
        $kelasRegulers = KelasReguler::with('guruPengampu')
            ->withCount(['siswas as total_siswa' => function($q) {
                $q->where('status', 'aktif');
            }])
            ->with(['siswas' => function($q) {
                $q->where('status', 'aktif')->with(['kelasTartil.guru', 'kelasTartil']);
            }])
            ->where('is_aktif', true)
            ->orderBy('jenjang')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        // Siswa aktif yang belum punya kelas reguler (untuk form daftar)
        $siswaBelumPunyaKelas = Siswa::where('status', 'aktif')
            ->whereNull('kelas_reguler_id')
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis']);

        return view('admin.kelas-reguler.keterangan', compact('kelasRegulers', 'siswaBelumPunyaKelas'));
    }

    // Export seluruh data siswa per kelas reguler ke Excel
    public function kelasRegulerExport()
    {
        $kelasRegulers = KelasReguler::with('guruPengampu')
            ->with(['siswas' => function($q) {
                $q->where('status', 'aktif')->with(['kelasTartil.guru', 'kelasTartil']);
            }])
            ->where('is_aktif', true)
            ->orderBy('jenjang')
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Keterangan Kelas');

        // Header style
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4A5568']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '2D3748']]],
        ];
        $subHeaderStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E9F0E9']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'A0AEC0']]],
        ];
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E0']]],
        ];

        $sheet->setCellValue('A1', 'KETERANGAN KELAS REGULER - DATA SISWA');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $row = 3;
        foreach ($kelasRegulers as $kr) {
            // Kelas header
            $sheet->setCellValue("A{$row}", "Kelas: {$kr->nama}");
            $sheet->setCellValue("D{$row}", "Jenjang: {$kr->jenjang} | Rombel: {$kr->tingkat}");
            $sheet->setCellValue("F{$row}", "Total Siswa: {$kr->siswas->count()}");
            $guruNama = $kr->guruPengampu ? $kr->guruPengampu->nama : 'Belum ditentukan';
            $sheet->setCellValue("H{$row}", "Guru Pengampu: {$guruNama}");
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($subHeaderStyle);
            $row++;

            if ($kr->siswas->count() === 0) {
                $sheet->setCellValue("A{$row}", 'Tidak ada siswa aktif di kelas ini.');
                $sheet->mergeCells("A{$row}:H{$row}");
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($dataStyle);
                $row += 2;
                continue;
            }

            // Column headers
            $sheet->setCellValue("A{$row}", 'No');
            $sheet->setCellValue("B{$row}", 'NIS');
            $sheet->setCellValue("C{$row}", 'Nama');
            $sheet->setCellValue("D{$row}", 'L/P');
            $sheet->setCellValue("E{$row}", 'Kelas Tartil');
            $sheet->setCellValue("F{$row}", 'Jenis Kelas Tartil');
            $sheet->setCellValue("G{$row}", 'Guru Tartil');
            $sheet->setCellValue("H{$row}", 'Status');
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($headerStyle);
            $row++;

            // Data rows
            foreach ($kr->siswas as $i => $s) {
                $sheet->setCellValue("A{$row}", $i + 1);
                $sheet->setCellValue("B{$row}", $s->nis);
                $sheet->setCellValue("C{$row}", $s->nama);
                $sheet->setCellValue("D{$row}", $s->jenis_kelamin == 'L' ? 'L' : 'P');
                $sheet->setCellValue("E{$row}", $s->kelasTartil ? $s->kelasTartil->nama : 'Belum masuk');
                $sheet->setCellValue("F{$row}", $s->kelasTartil ? ($s->kelasTartil->jenis ?? '-') : '-');
                $sheet->setCellValue("G{$row}", ($s->kelasTartil && $s->kelasTartil->guru) ? $s->kelasTartil->guru->nama : '-');
                $sheet->setCellValue("H{$row}", ucfirst($s->status));
                $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($dataStyle);
                $row++;
            }

            $row++; // spacing antar kelas
        }

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(18);
        $sheet->getColumnDimension('G')->setWidth(20);
        $sheet->getColumnDimension('H')->setWidth(12);

        $filename = 'keterangan_kelas_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function kelasRegulerDetail(KelasReguler $kelasReguler)
    {
        $siswas = Siswa::where('kelas_reguler_id', $kelasReguler->id)
            ->where('status', 'aktif')
            ->with(['kelasTartil.guru', 'kelasReguler.guruPengampu'])
            ->orderBy('nama')
            ->paginate(30);

        return view('admin.kelas-reguler.detail', compact('kelasReguler', 'siswas'));
    }

    // ==================== DAFTARKAN SISWA KE KELAS REGULER ====================
    public function kelasRegulerDaftarkanSiswa(Request $request, KelasReguler $kelasReguler)
    {
        if (!$kelasReguler->is_aktif) {
            return back()->with('error', 'Kelas reguler tidak aktif, tidak bisa mendaftarkan siswa.');
        }

        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
        ]);

        $count = 0;
        foreach ($request->siswa_ids as $siswaId) {
            $siswa = Siswa::find($siswaId);
            if ($siswa->status !== 'aktif') continue;

            $kelasLama = $siswa->kelas_reguler_id;
            $siswa->update(['kelas_reguler_id' => $kelasReguler->id]);
            $count++;

            RiwayatMutasi::create([
                'mutasi_type' => Siswa::class,
                'mutasi_id' => $siswa->id,
                'jenis' => 'pindah_kelas_reguler',
                'keterangan' => "Daftar ke kelas {$kelasReguler->nama} ({$kelasReguler->jenjang}{$kelasReguler->tingkat})" . ($kelasLama ? ", dari kelas lama" : ", sebelumnya belum punya kelas"),
                'dilakukan_oleh' => auth()->id(),
                'tanggal_mutasi' => now(),
            ]);
        }

        return back()->with('success', "{$count} siswa berhasil didaftarkan ke kelas {$kelasReguler->nama}.");
    }

    // ==================== PINDAH KELAS REGULER ====================
    public function kelasRegulerPindah(Request $request)
    {
        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
            'kelas_reguler_baru_id' => 'required|exists:kelas_regulers,id',
        ]);

        $kelasBaru = KelasReguler::findOrFail($request->kelas_reguler_baru_id);
        if (!$kelasBaru->is_aktif) {
            return back()->with('error', 'Kelas reguler tujuan tidak aktif.');
        }

        $count = 0;
        foreach ($request->siswa_ids as $siswaId) {
            $siswa = Siswa::find($siswaId);
            if ($siswa->status !== 'aktif') continue;

            $kelasLamaNama = $siswa->kelasReguler?->nama ?? 'Tanpa Kelas';
            $siswa->update(['kelas_reguler_id' => $kelasBaru->id]);
            $count++;

            RiwayatMutasi::create([
                'mutasi_type' => Siswa::class,
                'mutasi_id' => $siswa->id,
                'jenis' => 'pindah_kelas_reguler',
                'keterangan' => "Pindah dari {$kelasLamaNama} ke {$kelasBaru->nama} ({$kelasBaru->jenjang}{$kelasBaru->tingkat})",
                'dilakukan_oleh' => auth()->id(),
                'tanggal_mutasi' => now(),
            ]);
        }

        return back()->with('success', "{$count} siswa berhasil dipindahkan ke kelas {$kelasBaru->nama}.");
    }

    // ==================== HALAMAN PINDAH KELAS REGULER ====================
    // Alur: Pilih Kelas Asal → Pilih Kelas Tujuan → Checkbox Siswa → Submit
    public function kelasRegulerPindahIndex(Request $request)
    {
        $kelasRegulers = KelasReguler::where('is_aktif', true)
            ->withCount(['siswas as total_siswa' => fn($q) => $q->where('status', 'aktif')])
            ->orderBy('jenjang')
            ->orderBy('tingkat')
            ->get();

        $kelasAsal = null;
        $kelasTujuan = null;
        $siswaList = collect();
        $step = 1;

        // Step 2: Kelas asal dan tujuan sudah dipilih, tampilkan siswa
        if ($request->filled('kelas_asal_id') && $request->filled('kelas_tujuan_id')) {
            $kelasAsal = KelasReguler::find($request->kelas_asal_id);
            $kelasTujuan = KelasReguler::find($request->kelas_tujuan_id);

            if ($kelasAsal && $kelasTujuan) {
                $siswaList = Siswa::where('kelas_reguler_id', $kelasAsal->id)
                    ->where('status', 'aktif')
                    ->with('kelasTartil')
                    ->orderBy('nama')
                    ->get();
                $step = 3;
            }
        }
        // Step 1: Pilih kelas asal dan tujuan
        elseif ($request->filled('kelas_asal_id')) {
            $kelasAsal = KelasReguler::find($request->kelas_asal_id);
            $step = 2;
        }

        return view('admin.kelas-reguler.pindah', compact(
            'kelasRegulers', 'kelasAsal', 'kelasTujuan', 'siswaList', 'step'
        ));
    }

    // ============ KETERANGAN KELAS TARTIL (dengan detail siswa) ============
    public function rekapKelasTartil()
    {
        $semesterAktif = Semester::aktif()->first();

        if (!$semesterAktif) {
            return view('admin.kelas.rekap', [
                'semesterAktif' => null,
                'kelasList' => collect(),
                'totalSiswa' => 0,
                'jumlahPerJenis' => collect(),
            ]);
        }

        // Ambil kelas tartil aktif dengan siswa detail
        $kelasList = Kelas::where('status', 'aktif')
            ->with('guru')
            ->with(['siswas' => fn($q) => $q->where('status', 'aktif')->with(['kelasReguler'])->orderBy('nama')])
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        // Total siswa aktif di semua kelas tartil
        $totalSiswa = Siswa::where('status', 'aktif')->whereNotNull('kelas_tartil_id')->count();

        // Jumlah siswa per jenis kelas
        $jumlahPerJenis = $kelasList->groupBy('jenis')->map(function($kelasGroup) {
            return $kelasGroup->sum('siswas_count');
        });

        return view('admin.kelas.rekap', compact('semesterAktif', 'kelasList', 'totalSiswa', 'jumlahPerJenis'));
    }

    // Export seluruh data siswa per kelas tartil ke Excel
    public function keteranganKelasTartilExport()
    {
        $kelasList = Kelas::where('status', 'aktif')
            ->with('guru')
            ->with(['siswas' => fn($q) => $q->where('status', 'aktif')->with(['kelasReguler'])->orderBy('nama')])
            ->withCount(['siswas' => fn($q) => $q->where('status', 'aktif')])
            ->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')")
            ->orderBy('nama')
            ->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Keterangan Kelas Tartil');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4A5568']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => '2D3748']]],
        ];
        $subHeaderStyle = [
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E9F0E9']],
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'A0AEC0']]],
        ];
        $dataStyle = [
            'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'CBD5E0']]],
        ];

        $sheet->setCellValue('A1', 'KETERANGAN KELAS TARTIL - DATA LENGKAP');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $row = 3;
        foreach ($kelasList as $kr) {
            $guruNama = $kr->guru ? $kr->guru->nama : 'Belum ditentukan';
            $totalSiswa = $kr->siswas->count();

            $sheet->setCellValue("A{$row}", "Kelas: {$kr->nama}");
            $sheet->setCellValue("D{$row}", "Jenis: {$kr->jenis}");
            $sheet->setCellValue("F{$row}", "Total: {$totalSiswa} siswa");
            $sheet->setCellValue("H{$row}", "Guru: {$guruNama}");
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($subHeaderStyle);
            $row++;

            if ($totalSiswa === 0) {
                $sheet->setCellValue("A{$row}", 'Tidak ada siswa aktif di kelas ini.');
                $sheet->mergeCells("A{$row}:I{$row}");
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($dataStyle);
                $row += 2;
                continue;
            }

            $sheet->setCellValue("A{$row}", 'No');
            $sheet->setCellValue("B{$row}", 'NIS');
            $sheet->setCellValue("C{$row}", 'Nama');
            $sheet->setCellValue("D{$row}", 'L/P');
            $sheet->setCellValue("E{$row}", 'Kelas Reguler');
            $sheet->setCellValue("F{$row}", 'No HP');
            $sheet->setCellValue("G{$row}", 'Status');
            $sheet->setCellValue("H{$row}", 'Tanggal Masuk');
            $sheet->setCellValue("I{$row}", 'Tanggal Lahir');
            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($headerStyle);
            $row++;

            foreach ($kr->siswas as $i => $s) {
                $sheet->setCellValue("A{$row}", $i + 1);
                $sheet->setCellValue("B{$row}", $s->nis);
                $sheet->setCellValue("C{$row}", $s->nama);
                $sheet->setCellValue("D{$row}", $s->jenis_kelamin == 'L' ? 'L' : 'P');
                $sheet->setCellValue("E{$row}", $s->kelasReguler ? $s->kelasReguler->nama : '-');
                $sheet->setCellValue("F{$row}", $s->no_hp ?? '-');
                $sheet->setCellValue("G{$row}", ucfirst($s->status));
                $sheet->setCellValue("H{$row}", $s->tanggal_masuk ? $s->tanggal_masuk->format('d/m/Y') : '-');
                $sheet->setCellValue("I{$row}", $s->tanggal_lahir ? $s->tanggal_lahir->format('d/m/Y') : '-');
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($dataStyle);
                $row++;
            }

            $row++;
        }

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(6);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);

        $filename = 'keterangan_kelas_tartil_' . date('Ymd_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ============ RIWAYAT SISWA PER SEMESTER ============
    public function riwayatSiswaIndex(Request $request)
    {
        $query = Siswa::with(['kelasReguler', 'kelasTartil'])->withCount('semesters');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($sq) use ($q) {
                $sq->where('nama', 'like', "%{$q}%")
                   ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        if ($request->filled('kelas_reguler')) {
            $query->where('kelas_reguler_id', $request->kelas_reguler);
        }

        $siswas = $query->orderBy('nama')->paginate(30);
        $kelasRegulars = KelasReguler::where('is_aktif', true)->orderBy('nama')->get();

        return view('admin.riwayat-siswa.index', compact('siswas', 'kelasRegulars'));
    }

    public function riwayatSiswaDetail(Siswa $siswa)
    {
        $siswa->load(['semesters' => function ($q) {
            $q->orderBy('tanggal_mulai', 'desc');
        }]);

        $records = \App\Models\SemesterSiswa::where('siswa_id', $siswa->id)
            ->with(['semester', 'kelasTartil', 'kelasReguler'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.riwayat-siswa.detail', compact('siswa', 'records'));
    }

    // ============ GURU REGULER CRUD ============
    public function guruRegulerIndex()
    {
        $gurus = GuruReguler::orderBy('nama')->paginate(20);
        return view('admin.guru-reguler.index', compact('gurus'));
    }

    public function guruRegulerStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100',
            'nip' => 'nullable|max:30|unique:guru_regulers,nip',
            'email' => 'required|email|unique:guru_regulers,email',
            'no_hp' => 'required|max:15',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'nip.max' => 'NIP maksimal 30 karakter.',
            'nip.unique' => 'NIP sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'no_hp.required' => 'No HP wajib diisi.',
            'no_hp.max' => 'No HP maksimal 15 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
        ]);
        $validated['is_aktif'] = true;
        GuruReguler::create($validated);
        return redirect()->route('admin.guru-reguler.index')->with('success', 'Guru reguler "' . $validated['nama'] . '" berhasil ditambahkan.');
    }

    public function guruRegulerEdit(GuruReguler $guruReguler)
    {
        return view('admin.guru-reguler.edit', compact('guruReguler'));
    }

    public function guruRegulerUpdate(Request $request, GuruReguler $guruReguler)
    {
        $validated = $request->validate([
            'nama' => 'required|max:100',
            'nip' => 'nullable|max:30|unique:guru_regulers,nip,' . $guruReguler->id,
            'email' => 'required|email|unique:guru_regulers,email,' . $guruReguler->id,
            'no_hp' => 'required|max:15',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'nullable',
            'is_aktif' => 'boolean',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'nip.max' => 'NIP maksimal 30 karakter.',
            'nip.unique' => 'NIP sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'no_hp.required' => 'No HP wajib diisi.',
            'no_hp.max' => 'No HP maksimal 15 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
        ]);

        $validated['is_aktif'] = $request->has('is_aktif');
        $guruReguler->update($validated);
        return redirect()->route('admin.guru-reguler.index')->with('success', 'Data guru reguler "' . $validated['nama'] . '" berhasil diperbarui.');
    }

    // ============ TAHUN AJARAN BARU (AUTO SEMUA) ============
    public function tahunAjaranIndex()
    {
        $tahunAjarans = TahunAjaran::withCount('semesters')->orderBy('nama', 'desc')->paginate(20);
        $semesters = Semester::with('tahunAjaran')->withCount(['kelasTartils', 'siswas'])->orderBy('tanggal_mulai', 'desc')->paginate(20);
        $taAktif = TahunAjaran::aktif()->first();
        return view('admin.tahun-ajaran.index', compact('tahunAjarans', 'semesters', 'taAktif'));
    }

    public function tahunAjaranStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|max:20|unique:tahun_ajaran,nama',
            'tanggal_mulai' => 'required|date',
        ]);

        // === VALIDASI: Hanya boleh 1x per TA baru ===
        // 1. Cek apakah sudah ada semester untuk TA ini
        $semesterSudahAda = Semester::where('tahun_ajaran', $validated['nama'])->exists();
        if ($semesterSudahAda) {
            return back()->with('error', 'TA "' . $validated['nama'] . '" sudah pernah dibuat. Pembuatan TA hanya boleh dilakukan sekali.');
        }

        // 2. Cek apakah ada TA yang benar-benar aktif (masih punya semester aktif)
        $taAktif = TahunAjaran::aktif()->first();
        if ($taAktif && $taAktif->isBenarAktif()) {
            return back()->with('error', 'TA "' . $taAktif->nama . '" masih aktif. Tutup semesternya terlebih dahulu sebelum membuat TA baru.');
        }
        // Jika TA "aktif" tapi semua semesternya sudah ditutup, otomatis tutup TA-nya
        if ($taAktif && $taAktif->isSemuaSemesterDitutup()) {
            $taAktif->update(['status' => 'ditutup']);
        }

        // 3. Cek apakah ada semester aktif
        $semesterAktif = Semester::aktif()->first();
        if ($semesterAktif) {
            return back()->with('error', 'Semester "' . $semesterAktif->nama . '" masih aktif. Nonaktifkan semester lama terlebih dahulu.');
        }

        // 4. VALIDASI: Kelas aktif jenjang 1-5 yang punya siswa harus punya kelas tujuan (jenjang+1, rombel sama)
        $kelasTanpaTujuan = [];
        for ($jenjang = 5; $jenjang >= 1; $jenjang--) {
            $kelasLamaList = KelasReguler::where('jenjang', $jenjang)->where('is_aktif', true)->get();
            foreach ($kelasLamaList as $kl) {
                // Hanya validasi kelas yang punya siswa aktif
                $jumlahSiswa = Siswa::where('kelas_reguler_id', $kl->id)->where('status', 'aktif')->count();
                if ($jumlahSiswa === 0) continue; // kelas kosong, skip validasi

                $kb = KelasReguler::where('jenjang', $jenjang + 1)
                    ->where('tingkat', $kl->tingkat)
                    ->where('is_aktif', true)
                    ->first();
                if (!$kb) {
                    $kelasTanpaTujuan[] = "{$kl->nama} (jenjang {$jenjang} → " . ($jenjang + 1) . ", rombel {$kl->tingkat}, {$jumlahSiswa} siswa)";
                }
            }
        }
        if (!empty($kelasTanpaTujuan)) {
            $daftar = implode(', ', $kelasTanpaTujuan);
            return back()->with('error', 'Pembuatan TA dibatalkan. Kelas tujuan tidak ditemukan untuk: ' . $daftar . '. Silakan buat kelas tujuan terlebih dahulu di menu Kelas Reguler.');
        }

        return \DB::transaction(function () use ($validated) {
            $log = [];

            // === STEP 1: Tutup TA lama + semester lama ===
            $taLama = TahunAjaran::aktif()->first();
            if ($taLama) {
                // Nonaktifkan semester lama
                Semester::where('is_aktif', true)->update([
                    'is_aktif' => false,
                    'status' => 'ditutup',
                ]);
                // Tutup TA lama
                $taLama->update(['status' => 'ditutup']);
                $log[] = "TA {$taLama->nama} ditutup";
            }

            // === STEP 2: Proses kenaikan kelas reguler ===
            $countNaik = 0;
            $countLulus = 0;

            // Kelas 6 → Lulus (semua)
            $kelas6List = KelasReguler::where('jenjang', 6)->where('is_aktif', true)->get();
            foreach ($kelas6List as $k6) {
                $siswaKelas6 = Siswa::where('kelas_reguler_id', $k6->id)->where('status', 'aktif')->get();
                foreach ($siswaKelas6 as $s) {
                    $s->update([
                        'status' => 'lulus',
                        'keterangan_status' => 'Lulus jenjang 6',
                        'kelas_reguler_id' => null,
                        'kelas_tartil_id' => null,
                        'tanggal_masuk_kelas_tartil' => null,
                    ]);
                    $countLulus++;
                }
            }

            // Kelas 5→6, 4→5, 3→4, 2→3, 1→2 (rombel tetap)
            // Sudah divalidasi di atas, semua kelas punya tujuan
            for ($jenjang = 5; $jenjang >= 1; $jenjang--) {
                $kelasLamaList = KelasReguler::where('jenjang', $jenjang)->where('is_aktif', true)->get();
                foreach ($kelasLamaList as $kl) {
                    $kb = KelasReguler::where('jenjang', $jenjang + 1)
                        ->where('tingkat', $kl->tingkat)
                        ->where('is_aktif', true)
                        ->first();
                    if (!$kb) continue; // skip kalau tidak ada kelas tujuan (kelas kosong)

                    $siswaList = Siswa::where('kelas_reguler_id', $kl->id)->where('status', 'aktif')->get();
                    foreach ($siswaList as $s) {
                        // Naik kelas reguler + reset status mutasi (jadi reguler di TA baru)
                        $s->update([
                            'kelas_reguler_id' => $kb->id,
                            'tanggal_masuk_kelas_tartil' => null,
                        ]);
                        $countNaik++;
                    }
                }
            }
            $log[] = "Kenaikan: {$countLulus} lulus, {$countNaik} naik";

            // === STEP 3: Buat TA baru ===
            $tglMulai = \Carbon\Carbon::parse($validated['tanggal_mulai']);
            $tglSelesai = $tglMulai->copy()->addYear()->subDay();

            $taBaru = TahunAjaran::create([
                'nama' => $validated['nama'],
                'tanggal_mulai' => $tglMulai,
                'tanggal_selesai' => $tglSelesai,
                'status' => 'aktif',
            ]);
            $log[] = "TA {$taBaru->nama} dibuat";

            // === STEP 4: Buat semester ganjil + genap ===
            $semGanjil = Semester::create([
                'tahun_ajaran_id' => $taBaru->id,
                'tahun_ajaran' => $taBaru->nama,
                'jenis' => 'ganjil',
                'tanggal_mulai' => $tglMulai,
                'tanggal_selesai' => $tglMulai->copy()->addMonths(6)->subDay(),
                'is_aktif' => true,
                'status' => 'aktif',
            ]);
            $log[] = "Semester Ganjil {$semGanjil->nama} dibuat & diaktifkan";

            $semGenap = Semester::create([
                'tahun_ajaran_id' => $taBaru->id,
                'tahun_ajaran' => $taBaru->nama,
                'jenis' => 'genap',
                'tanggal_mulai' => $tglMulai->copy()->addMonths(6),
                'tanggal_selesai' => $tglSelesai,
                'is_aktif' => false,
                'status' => 'nonaktif',
            ]);
            $log[] = "Semester Genap {$semGenap->nama} dibuat";

            // === STEP 5: Snapshot kelas tartil + siswa aktif ke semester ganjil ===
            $kelasTartil = Kelas::where('status', 'aktif')->get();
            foreach ($kelasTartil as $k) {
                $jumlah = Siswa::where('kelas_tartil_id', $k->id)->where('status', 'aktif')->count();
                SemesterKelas::create([
                    'semester_id' => $semGanjil->id,
                    'kelas_id' => $k->id,
                    'jumlah_siswa' => $jumlah,
                    'keterangan' => "Snapshot TA {$taBaru->nama} Ganjil",
                ]);
            }

            $siswaAktif = Siswa::where('status', 'aktif')->get();
            foreach ($siswaAktif as $s) {
                SemesterSiswa::create([
                    'semester_id' => $semGanjil->id,
                    'siswa_id' => $s->id,
                    'kelas_id' => $s->kelas_tartil_id,
                    'kelas_reguler_id' => $s->kelas_reguler_id,
                    'status_siswa' => 'aktif',
                    'keterangan' => "Snapshot TA {$taBaru->nama} Ganjil",
                ]);
            }
            $log[] = "Snapshot: {$kelasTartil->count()} kelas, {$siswaAktif->count()} siswa ke semester ganjil";

            return redirect()->route('admin.tahun-ajaran.index')
                ->with('success', "TA {$taBaru->nama} berhasil dibuat. " . implode('. ', $log) . '.');
        });
    }

    public function semesterTutup(Semester $semester)
    {
        $userId = auth()->id();
        $logDetails = [];

        // ════════════════════════════════════════════
        // STEP 1: SNAPSHOT KOP SURAT
        // ════════════════════════════════════════════
        try {
            KopSuratRapor::snapshotSemester($semester->id);
            SemesterAuditLog::log($semester, 'kop_surat', 'snapshot', 1, [], $userId);
            $logDetails[] = 'Kop surat di-arsip';
        } catch (\Throwable $e) {
            SemesterAuditLog::log($semester, 'kop_surat', 'snapshot', 0, ['error' => $e->getMessage()], $userId);
            $logDetails[] = 'Kop surat gagal di-arsip';
        }

        // ════════════════════════════════════════════
        // STEP 2: AMBIL SEMUA SISWA YANG PERNAH ADA DI SEMESTER INI
        // Menggunakan semester_siswa (historical), BUKAN siswas (current state).
        // Ini memastikan siswa yang pindah/mutasi/lulus di tengah semester
        // tetap memiliki track record yang di-lock.
        // ════════════════════════════════════════════
        $semesterSiswaRecords = SemesterSiswa::where('semester_id', $semester->id)
            ->with('siswa')
            ->get();

        // Jika semester_siswa kosong, fallback ke siswa aktif (backward compat)
        if ($semesterSiswaRecords->isEmpty()) {
            $aktifSiswas = Siswa::where('status', 'aktif')->get();
            foreach ($aktifSiswas as $siswa) {
                SemesterSiswa::firstOrCreate(
                    ['semester_id' => $semester->id, 'siswa_id' => $siswa->id],
                    ['kelas_id' => $siswa->kelas_tartil_id, 'kelas_reguler_id' => $siswa->kelas_reguler_id]
                );
            }
            $semesterSiswaRecords = SemesterSiswa::where('semester_id', $semester->id)
                ->with('siswa')
                ->get();
        }

        // ════════════════════════════════════════════
        // STEP 3: SNAPSHOT REKAP R2 AKHIR — lock nilai rapor
        // ════════════════════════════════════════════
        $rekapCount = 0;
        foreach ($semesterSiswaRecords as $ss) {
            $siswa = $ss->siswa;
            if (!$siswa) continue;
            $kelasId = $ss->kelas_id ?? $siswa->kelas_tartil_id;
            if (!$kelasId) continue;
            $kelas = Kelas::find($kelasId);
            if (!$kelas) continue;

            try {
                RekapR2Akhir::calculateAndSave($siswa, $semester, $kelas);
                $rekapCount++;
            } catch (\Throwable $e) {
                // Skip siswa yang gagal
            }
        }
        SemesterAuditLog::log($semester, 'r2', 'snapshot', $rekapCount, [], $userId);
        $logDetails[] = "R2 {$rekapCount} siswa di-lock";

        // ════════════════════════════════════════════
        // STEP 4: SNAPSHOT JURNAL HARIAN — lock track record mengaji
        // ════════════════════════════════════════════
        $jurnalCount = 0;
        foreach ($semesterSiswaRecords as $ss) {
            $siswa = $ss->siswa;
            if (!$siswa) continue;
            $kelasId = $ss->kelas_id ?? $siswa->kelas_tartil_id;
            if (!$kelasId) continue;
            $kelas = Kelas::find($kelasId);
            if (!$kelas) continue;

            try {
                RekapJurnalSemester::snapshot($siswa, $semester, $kelas);
                $jurnalCount++;
            } catch (\Throwable $e) {
                // Skip
            }
        }
        SemesterAuditLog::log($semester, 'jurnal', 'snapshot', $jurnalCount, [], $userId);
        $logDetails[] = "Jurnal {$jurnalCount} siswa di-lock";

        // ════════════════════════════════════════════
        // STEP 5: SNAPSHOT MUNAQOSYAH — lock ujian & nilai
        // ════════════════════════════════════════════
        $munaqosyahCount = 0;
        foreach ($semesterSiswaRecords as $ss) {
            $siswa = $ss->siswa;
            if (!$siswa) continue;

            try {
                RekapMunaqosyahSemester::snapshot($siswa, $semester);
                $munaqosyahCount++;
            } catch (\Throwable $e) {
                // Skip
            }
        }
        SemesterAuditLog::log($semester, 'munaqosyah', 'snapshot', $munaqosyahCount, [], $userId);
        $logDetails[] = "Munaqosyah {$munaqosyahCount} siswa di-lock";

        // ════════════════════════════════════════════
        // STEP 6: SNAPSHOT RIWAYAT KELAS — lock perpindahan & kenaikan
        // ════════════════════════════════════════════
        $riwayatCount = 0;
        foreach ($semesterSiswaRecords as $ss) {
            $siswa = $ss->siswa;
            if (!$siswa) continue;

            try {
                RekapRiwayatSemester::snapshot($siswa, $semester);
                $riwayatCount++;
            } catch (\Throwable $e) {
                // Skip
            }
        }
        SemesterAuditLog::log($semester, 'riwayat', 'snapshot', $riwayatCount, [], $userId);
        $logDetails[] = "Riwayat {$riwayatCount} siswa di-lock";

        // ════════════════════════════════════════════
        // STEP 7: SNAPSHOT TAHFIDZ — lock hafalan siswa kelas Tahfidz
        // ════════════════════════════════════════════
        $tahfidzCount = 0;
        foreach ($semesterSiswaRecords as $ss) {
            $siswa = $ss->siswa;
            if (!$siswa) continue;
            // Hanya snapshot kalau siswa di kelas Tahfidz
            $kelasId = $ss->kelas_id ?? $siswa->kelas_tartil_id;
            if (!$kelasId) continue;
            $kelas = Kelas::find($kelasId);
            if (!$kelas || $kelas->jenis !== 'Tahfidz') continue;

            try {
                RekapTahfidzSemester::snapshot($siswa, $semester);
                $tahfidzCount++;
            } catch (\Throwable $e) {
                // Skip
            }
        }
        SemesterAuditLog::log($semester, 'tahfidz', 'snapshot', $tahfidzCount, [], $userId);
        $logDetails[] = "Tahfidz {$tahfidzCount} siswa di-lock";

        // ════════════════════════════════════════════
        // STEP 8: TUTUP SEMESTER
        // ════════════════════════════════════════════
        $semester->update(['status' => 'ditutup', 'is_aktif' => false]);

        // Jika semua semester di TA sudah ditutup, tutup TA juga
        if ($semester->tahun_ajaran) {
            $allClosed = Semester::where('tahun_ajaran', $semester->tahun_ajaran)
                ->where('status', '!=', 'ditutup')
                ->doesntExist();
            if ($allClosed) {
                TahunAjaran::where('nama', $semester->tahun_ajaran)->update(['status' => 'ditutup']);
            }
        }

        $msg = "Semester {$semester->nama} ditutup. " . implode('. ', $logDetails) . '.';
        return back()->with('success', $msg);
    }

    /**
     * Hapus semester — DIBLOKIR untuk proteksi data.
     * Semester yang sudah punya data jurnal/munaqosyah/rapor tidak boleh dihapus.
     */
    public function semesterDestroy(Semester $semester)
    {
        // Cek apakah semester punya data jurnal
        $jurnalCount = \App\Models\JurnalHarian::where('semester_id', $semester->id)->count();
        if ($jurnalCount > 0) {
            return back()->with('error', "Semester tidak bisa dihapus karena memiliki {$jurnalCount} data jurnal. Gunakan 'Tutup' untuk menutup semester.");
        }

        // Cek munaqosyah
        $munaqosyahCount = \App\Models\UjianMunaqosyah::where('semester_id', $semester->id)->count();
        if ($munaqosyahCount > 0) {
            return back()->with('error', "Semester tidak bisa dihapus karena memiliki {$munaqosyahCount} data munaqosyah.");
        }

        // Cek penilaian rapor
        $raporCount = \App\Models\PenilaianRaporInternal::where('semester_id', $semester->id)->count();
        if ($raporCount > 0) {
            return back()->with('error', "Semester tidak bisa dihapus karena memiliki {$raporCount} data penilaian rapor.");
        }

        // Hanya hapus kalau benar-benar kosong
        $semester->delete();
        return back()->with('success', 'Semester berhasil dihapus.');
    }

    /**
     * Tutup Tahun Ajaran secara manual.
     */
    public function tahunAjaranTutup(TahunAjaran $tahunAjaran)
    {
        // Tutup semua semester dalam TA ini
        Semester::where('tahun_ajaran', $tahunAjaran->nama)
            ->update(['status' => 'ditutup', 'is_aktif' => false]);

        // Tutup TA
        $tahunAjaran->update(['status' => 'ditutup']);

        return back()->with('success', 'TA ' . $tahunAjaran->nama . ' berhasil ditutup.');
    }

    // ============ PENGATURAN KOP SURAT RAPOR ============
    public function kopSuratRaporIndex()
    {
        $kop = KopSuratRapor::getOrCreate(); // Ambil record terbaru, cleanup duplikat

        // Debug: Log semua record untuk identifikasi duplikat
        $all = KopSuratRapor::whereNull('semester_id')->orderBy('updated_at', 'desc')->get(['id', 'sub_judul', 'updated_at']);
        if ($all->count() > 1) {
            \Log::warning('KopSuratRapor duplikat: ' . $all->count() . ' record default ditemukan', $all->toArray());
        }

        return view('admin.kop-surat-rapor.index', compact('kop'));
    }

    public function kopSuratRaporUpdate(Request $request)
    {
        $kop = KopSuratRapor::getOrCreate(); // Ambil record default terbaru, cleanup duplikat

        $validated = $request->validate([
            'judul' => 'required|max:200',
            'sub_judul' => 'required|max:200',
            'nama_sekolah' => 'required|max:200',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:50',
            'email' => 'nullable|max:100',
            'website' => 'nullable|max:100',
            'tahun_ajaran' => 'nullable|max:20',
            'tanggal_cetak' => 'nullable|date',
            'catatan_kaki' => 'nullable',
            'kepala_sekolah' => 'nullable|max:100',
            'nip_kepala_sekolah' => 'nullable|max:50',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'stempel' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'ttd' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Upload logo baru
        if ($request->hasFile('logo')) {
            if ($kop->logo_path) {
                Storage::disk('public')->delete($kop->logo_path);
            }
            $validated['logo_path'] = $request->file('logo')->store('kop-surat', 'public');
        }

        // Upload stempel baru
        if ($request->hasFile('stempel')) {
            if ($kop->stempel_path) {
                Storage::disk('public')->delete($kop->stempel_path);
            }
            $validated['stempel_path'] = $request->file('stempel')->store('kop-surat', 'public');
        }

        // Upload ttd baru
        if ($request->hasFile('ttd')) {
            if ($kop->ttd_path) {
                Storage::disk('public')->delete($kop->ttd_path);
            }
            $validated['ttd_path'] = $request->file('ttd')->store('kop-surat', 'public');
        }

        // Hapus key file upload dari array (bukan column di DB)
        unset($validated['logo'], $validated['stempel'], $validated['ttd']);

        // Debug: Log sebelum update
        $idBefore = $kop->id;
        $subJudulBefore = $kop->sub_judul;
        \Log::info("KopSuratRapor UPDATE: id={$kop->id}, sub_judul_before='{$kop->sub_judul}', sub_judul_input='" . ($validated['sub_judul'] ?? 'NULL') . "'");

        // Update record
        $updated = $kop->update($validated);

        // Debug: Log setelah update
        $kopAfter = KopSuratRapor::find($kop->id);
        \Log::info("KopSuratRapor AFTER: id={$kop->id}, updated=" . ($updated ? 'true' : 'false') . ", sub_judul_after='" . ($kopAfter?->sub_judul ?? 'NULL') . "'");

        if (!$updated) {
            return redirect()->route('admin.kop-surat-rapor.index')
                ->with('error', 'Gagal menyimpan ke database. Cek log.');
        }

        return redirect()->route('admin.kop-surat-rapor.index')
            ->with('success', 'Pengaturan kop surat rapor berhasil diperbarui. [ID:' . $kop->id . ']');
    }

    // ═══════════════════════════════════════════════════════
    // AUDIT SEMESTER — Track Record Terkunci
    // ═══════════════════════════════════════════════════════

    /**
     * STEP 1: Pilih Tahun Ajaran.
     * Tampilkan semua TA dengan ringkasan statistik lengkap.
     */
    public function auditPilihTahunAjaran(Request $request)
    {
        $tahunAjaranList = TahunAjaran::orderBy('nama', 'desc')->get();

        if ($tahunAjaranList->isEmpty()) {
            $taFromSemesters = Semester::select('tahun_ajaran')
                ->distinct()
                ->orderBy('tahun_ajaran', 'desc')
                ->pluck('tahun_ajaran');

            $tahunAjaranList = $taFromSemesters->map(function ($nama) {
                return $this->buildTaStatistik($nama, true);
            });
        } else {
            $tahunAjaranList = $tahunAjaranList->map(function ($ta) {
                $stat = $this->buildTaStatistik($ta->nama, false);
                $ta->total_semester = $stat->total_semester;
                $ta->semester_ditutup = $stat->semester_ditutup;
                $ta->semester_aktif = $stat->semester_aktif;
                $ta->total_siswa = $stat->total_siswa;
                $ta->total_munaqosyah = $stat->total_munaqosyah;
                $ta->total_penilaian = $stat->total_penilaian;
                $ta->rata_r2_akhir = $stat->rata_r2_akhir;
                $ta->periode = $stat->periode;
                return $ta;
            });
        }

        return view('admin.audit-semester.pilih-ta', compact('tahunAjaranList'));
    }

    /**
     * Build statistik per TA.
     */
    private function buildTaStatistik(string $nama, bool $isVirtual): object
    {
        $semesters = Semester::where('tahun_ajaran', $nama)->get();
        $semesterIds = $semesters->pluck('id')->toArray();

        // Total siswa unique di semua semester TA ini
        $totalSiswa = SemesterSiswa::whereIn('semester_id', $semesterIds)
            ->distinct('siswa_id')
            ->count('siswa_id');

        // Total munaqosyah
        $totalMunaqosyah = UjianMunaqosyah::whereIn('semester_id', $semesterIds)->count();

        // Total penilaian
        $totalPenilaian = PenilaianRaporInternal::whereIn('semester_id', $semesterIds)->count();

        // Rata-rata R2 Akhir
        $rataR2 = RekapR2Akhir::whereIn('semester_id', $semesterIds)->avg('r2_akhir');

        // Periode
        $tglMulai = $semesters->min('tanggal_mulai');
        $tglSelesai = $semesters->max('tanggal_selesai');
        $periode = ($tglMulai ? $tglMulai->format('d/m/Y') : '-') . ' - ' . ($tglSelesai ? $tglSelesai->format('d/m/Y') : '-');

        $obj = (object) [
            'nama' => $nama,
            'status' => $semesters->where('status', 'aktif')->count() > 0 ? 'aktif' : 'ditutup',
            'total_semester' => $semesters->count(),
            'semester_ditutup' => $semesters->where('status', 'ditutup')->count(),
            'semester_aktif' => $semesters->where('status', 'aktif')->count(),
            'total_siswa' => $totalSiswa,
            'total_munaqosyah' => $totalMunaqosyah,
            'total_penilaian' => $totalPenilaian,
            'rata_r2_akhir' => $rataR2 ? round($rataR2, 2) : 0,
            'periode' => $periode,
            'is_virtual' => $isVirtual,
        ];

        return $obj;
    }

    /**
     * STEP 2: Pilih Semester dalam TA yang dipilih.
     * Tampilkan daftar semester + ringkasan per semester.
     */
    public function auditSemesterIndex(Request $request)
    {
        $selectedTa = $request->get('ta');

        // Ambil semester dalam TA ini
        $semesterList = Semester::where('tahun_ajaran', $selectedTa)
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        if ($semesterList->isEmpty()) {
            return redirect()->route('admin.audit-semester.pilih-ta')
                ->with('error', "Tidak ada semester untuk TA {$selectedTa}");
        }

        // Tambah ringkasan per semester
        $semesterList = $semesterList->map(function ($semester) {
            $semester->total_siswa = SemesterSiswa::where('semester_id', $semester->id)->count();
            $semester->total_munaqosyah = UjianMunaqosyah::where('semester_id', $semester->id)->count();
            $semester->total_penilaian = PenilaianRaporInternal::where('semester_id', $semester->id)->count();
            return $semester;
        });

        // Cek apakah ada request untuk lihat detail semester tertentu
        $selectedSemester = null;
        $rekapData = null;
        $semesterId = $request->get('semester_id');

        if ($semesterId) {
            $selectedSemester = $semesterList->firstWhere('id', (int)$semesterId);
            if ($selectedSemester) {
                $rekapData = $this->buildAuditRekap($selectedSemester);
            }
        }

        return view('admin.audit-semester.index', compact(
            'selectedTa', 'semesterList', 'selectedSemester', 'rekapData'
        ));
    }

    /**
     * Detail audit untuk 1 siswa di 1 semester.
     */
    public function auditSemesterDetail(Request $request, Semester $semester)
    {
        $siswaId = $request->get('siswa_id');
        if (!$siswaId) {
            return back()->with('error', 'Pilih siswa terlebih dahulu.');
        }

        $siswa = Siswa::with(['kelasReguler', 'kelasTartil'])->findOrFail($siswaId);

        // Ambil semua data snapshot
        $snapR2 = RekapR2Akhir::where('semester_id', $semester->id)
            ->where('siswa_id', $siswaId)
            ->first();
        $snapJurnal = RekapJurnalSemester::where('semester_id', $semester->id)
            ->where('siswa_id', $siswaId)
            ->first();
        $snapMunaqosyah = RekapMunaqosyahSemester::where('semester_id', $semester->id)
            ->where('siswa_id', $siswaId)
            ->first();
        $snapRiwayat = RekapRiwayatSemester::where('semester_id', $semester->id)
            ->where('siswa_id', $siswaId)
            ->first();

        // Audit log
        $auditLogs = SemesterAuditLog::where('semester_id', $semester->id)
            ->orderBy('locked_at', 'desc')
            ->get();

        return view('admin.audit-semester.detail', compact(
            'semester', 'siswa', 'snapR2', 'snapJurnal', 'snapMunaqosyah', 'snapRiwayat', 'auditLogs'
        ));
    }

    /**
     * Export PDF audit semester (rekap semua siswa).
     */
    public function auditSemesterExportPdf(Semester $semester)
    {
        $rekapData = $this->buildAuditRekap($semester);
        $kop = KopSuratRapor::untukSemester($semester->id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.audit-semester', compact('semester', 'rekapData', 'kop'))
            ->setPaper('A4', 'landscape');
        $taSafe = str_replace('/', '-', $semester->tahun_ajaran);
        $filename = "Audit_{$taSafe}_{$semester->jenis}_{$semester->id}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Export Excel audit semester (rekap semua siswa).
     * Multi-sheet: Ringkasan, Munaqosyah, Penilaian, Siswa per Kelas.
     */
    public function auditSemesterExportExcel(Semester $semester)
    {
        $rekapData = $this->buildAuditRekap($semester);

        $spreadsheet = new Spreadsheet();
        $styleBold = ['font' => ['bold' => true]];
        $styleHeader = ['font' => ['bold' => true, 'size' => 10], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']]];
        $styleGreen = ['font' => ['bold' => true, 'color' => ['rgb' => '0c8a5f']]];

        // ══════ SHEET 1: RINGKASAN ══════
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Ringkasan');
        $sheet1->setCellValue('A1', "AUDIT SEMESTER - {$semester->nama}");
        $sheet1->mergeCells('A1:D1');
        $sheet1->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '0c8a5f']]]);
        $sheet1->setCellValue('A2', "TA: {$semester->tahun_ajaran} | Periode: {$semester->tanggal_mulai?->format('d/m/Y')} - {$semester->tanggal_selesai?->format('d/m/Y')}");
        $sheet1->mergeCells('A2:D2');
        $sheet1->setCellValue('A3', 'Status Data: ' . ($semester->status === 'ditutup' ? 'TERKUNCI (Snapshot)' : 'REAL-TIME (Aktif)'));
        $sheet1->mergeCells('A3:D3');

        $r = 5;
        $sheet1->setCellValue("A{$r}", 'RINGKASAN UTAMA'); $sheet1->getStyle("A{$r}")->applyFromArray($styleGreen); $r++;
        $sheet1->setCellValue("A{$r}", 'Total Siswa'); $sheet1->setCellValue("B{$r}", $rekapData['totalSiswa'] ?? 0); $r++;
        $sheet1->setCellValue("A{$r}", 'R2 Akhir Rata-rata'); $sheet1->setCellValue("B{$r}", $rekapData['rataR2Akhir'] ?? 0); $r++;
        $sheet1->setCellValue("A{$r}", 'Mengaji Rata-rata (hari)'); $sheet1->setCellValue("B{$r}", $rekapData['rataMengaji'] ?? 0); $r++;
        $sheet1->setCellValue("A{$r}", 'Total Munaqosyah'); $sheet1->setCellValue("B{$r}", count($rekapData['munaqosyahList'] ?? [])); $r++;
        $sheet1->setCellValue("A{$r}", 'Total Penilaian Rapor'); $sheet1->setCellValue("B{$r}", count($rekapData['penilaianList'] ?? [])); $r++;

        foreach (['A', 'B'] as $col) {
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        // ══════ SHEET 2: MUNAQOSYAH ══════
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Munaqosyah');
        $sheet2->setCellValue('A1', 'MUNAQOSYAH');
        $sheet2->getStyle('A1')->applyFromArray($styleGreen);

        $r = 3;
        foreach ($rekapData['munaqosyahList'] ?? [] as $mq) {
            $sheet2->setCellValue("A{$r}", $mq['ujian']->nama . ' (' . $mq['ujian']->tingkat . ')');
            $sheet2->getStyle("A{$r}")->applyFromArray($styleBold);
            $sheet2->setCellValue("B{$r}", 'Tanggal: ' . ($mq['ujian']->tanggal_ujian?->format('d/m/Y') ?? '-'));
            $r++;
            $sheet2->setCellValue("A{$r}", "Peserta: {$mq['total']} | Lulus: {$mq['lulus']} | Tidak Lulus: {$mq['tidakLulus']} | Rata-rata: {$mq['rataNilai']}");
            $sheet2->mergeCells("A{$r}:F{$r}");
            $r++;

            if (!empty($mq['peserta'])) {
                $headers = ['No', 'NIS', 'Nama', 'Nilai', 'Status', 'Catatan'];
                foreach ($headers as $i => $h) {
                    $col = chr(65 + $i);
                    $sheet2->setCellValue("{$col}{$r}", $h);
                    $sheet2->getStyle("{$col}{$r}")->applyFromArray($styleHeader);
                }
                $r++;
                foreach ($mq['peserta'] as $i => $p) {
                    $sheet2->setCellValue("A{$r}", $i + 1);
                    $sheet2->setCellValue("B{$r}", $p['siswa']->nis ?? '-');
                    $sheet2->setCellValue("C{$r}", $p['siswa']->nama ?? '-');
                    $sheet2->setCellValue("D{$r}", $p['nilai'] ?? '-');
                    $sheet2->setCellValue("E{$r}", $p['status'] === 'L' ? 'Lulus' : ($p['status'] === 'TL' ? 'Tidak Lulus' : 'Terdaftar'));
                    $sheet2->setCellValue("F{$r}", $p['catatan'] ?? '-');
                    $r++;
                }
            }
            $r += 2;
        }
        foreach (['A', 'B', 'C', 'D', 'E', 'F'] as $col) {
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        // ══════ SHEET 3: PENILAIAN RAPOR (per Kelas Tartil) ══════
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Penilaian Rapor');
        $sheet3->setCellValue('A1', 'PENILAIAN RAPOR');
        $sheet3->getStyle('A1')->applyFromArray($styleGreen);

        $r = 3;
        foreach ($rekapData['penilaianList'] ?? [] as $pn) {
            $sheet3->setCellValue("A{$r}", $pn['penilaian']->nama);
            $sheet3->getStyle("A{$r}")->applyFromArray($styleBold);
            $sheet3->setCellValue("B{$r}", $pn['totalSiswa'] . ' siswa total | Status: ' . $pn['penilaian']->status);
            $sheet3->mergeCells("B{$r}:F{$r}");
            $r++;

            // Loop per kelas tartil
            foreach ($pn['perKelasTartil'] ?? [] as $pkt) {
                // Header kelas tartil
                $sheet3->setCellValue("A{$r}", 'KELAS ' . $pkt['jenisKelas']);
                $sheet3->getStyle("A{$r}")->applyFromArray(['font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '0c8a5f']]]);
                $sheet3->setCellValue("B{$r}", $pkt['totalSiswa'] . ' siswa');
                $r++;

                // Indikator
                if (!empty($pkt['indikatorNames'])) {
                    $sheet3->setCellValue("A{$r}", 'Indikator: ' . implode(', ', $pkt['indikatorNames']));
                    $sheet3->mergeCells("A{$r}:F{$r}");
                    $r++;
                }

                // Tabel header
                if (!empty($pkt['nilaiPerSiswa'])) {
                    $sheet3->setCellValue("A{$r}", 'No'); $sheet3->getStyle("A{$r}")->applyFromArray($styleHeader);
                    $sheet3->setCellValue("B{$r}", 'NIS'); $sheet3->getStyle("B{$r}")->applyFromArray($styleHeader);
                    $sheet3->setCellValue("C{$r}", 'Nama'); $sheet3->getStyle("C{$r}")->applyFromArray($styleHeader);
                    $sheet3->setCellValue("D{$r}", 'Rata-rata'); $sheet3->getStyle("D{$r}")->applyFromArray($styleHeader);
                    $colIdx = 4;
                    if (!empty($pkt['nilaiPerSiswa'][0]['detail'])) {
                        foreach ($pkt['nilaiPerSiswa'][0]['detail'] as $d) {
                            $col = chr(65 + $colIdx);
                            $sheet3->setCellValue("{$col}{$r}", $d['indikator']);
                            $sheet3->getStyle("{$col}{$r}")->applyFromArray($styleHeader);
                            $colIdx++;
                        }
                    }
                    $r++;

                    // Data siswa
                    foreach ($pkt['nilaiPerSiswa'] as $i => $ns) {
                        $sheet3->setCellValue("A{$r}", $i + 1);
                        $sheet3->setCellValue("B{$r}", $ns['siswa']->nis ?? '-');
                        $sheet3->setCellValue("C{$r}", $ns['siswa']->nama ?? '-');
                        $sheet3->setCellValue("D{$r}", $ns['nilaiRata']);
                        $colIdx = 4;
                        foreach ($ns['detail'] as $d) {
                            $col = chr(65 + $colIdx);
                            $sheet3->setCellValue("{$col}{$r}", $d['nilai']);
                            $colIdx++;
                        }
                        $r++;
                    }
                }
                $r += 1; // Spasi antar kelas
            }
            $r += 2; // Spasi antar penilaian
        }
        foreach (range('A', 'Z') as $col) {
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        // ══════ SHEET 4: SISWA PER KELAS REGULER ══════
        $sheet4 = $spreadsheet->createSheet();
        $sheet4->setTitle('Siswa per Kelas');
        $sheet4->setCellValue('A1', 'SISWA PER KELAS REGULER');
        $sheet4->getStyle('A1')->applyFromArray($styleGreen);

        $r = 3;
        foreach ($rekapData['siswaPerKelasReguler'] ?? [] as $kelasRegulerNama => $siswaKelas) {
            $sheet4->setCellValue("A{$r}", $kelasRegulerNama . ' - ' . count($siswaKelas) . ' siswa');
            $sheet4->getStyle("A{$r}")->applyFromArray(['font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1565C0']]]);
            $sheet4->mergeCells("A{$r}:J{$r}");
            $r++;

            $headers = ['No', 'NIS', 'Nama', 'Kelas Tartil', 'R2 Harian', 'R2 Penilaian', 'R2 Akhir', 'Mengaji (hari)', 'B/C/K', 'Munaqosyah'];
            foreach ($headers as $i => $h) {
                $col = chr(65 + $i);
                $sheet4->setCellValue("{$col}{$r}", $h);
                $sheet4->getStyle("{$col}{$r}")->applyFromArray($styleHeader);
            }
            $r++;

            foreach ($siswaKelas as $i => $d) {
                $sheet4->setCellValue("A{$r}", $i + 1);
                $sheet4->setCellValue("B{$r}", $d['siswa']->nis);
                $sheet4->setCellValue("C{$r}", $d['siswa']->nama);
                $sheet4->setCellValue("D{$r}", $d['kelasTartil']);
                $sheet4->setCellValue("E{$r}", $d['r2Harian']);
                $sheet4->setCellValue("F{$r}", $d['r2Penilaian']);
                $sheet4->setCellValue("G{$r}", $d['r2Akhir']);
                $sheet4->setCellValue("H{$r}", $d['totalHari']);
                $sheet4->setCellValue("I{$r}", "{$d['countB']}/{$d['countC']}/{$d['countK']}");
                $sheet4->setCellValue("J{$r}", $d['munaqosyahStatus']);
                $r++;
            }
            $r += 2;
        }
        foreach (range('A', 'J') as $col) {
            $sheet4->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $taSafe = str_replace('/', '-', $semester->tahun_ajaran);
        $filename = "Audit_{$taSafe}_{$semester->jenis}_{$semester->id}.xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        $writer->save('php://output');
        exit;
    }

    /**
     * Build rekap audit data untuk 1 semester.
     * Mengambil dari SNAPSHOT (terkunci), bukan real-time.
     */
    private function buildAuditRekap(Semester $semester): array
    {
        // ════════════════════════════════════════════
        // SECTION 1: DATA SISWA + R2 + JURNAL
        // ════════════════════════════════════════════
        $siswaList = [];
        $siswaPerKelasReguler = [];
        $totalR2Akhir = 0;
        $totalMengaji = 0;
        $countSiswa = 0;

        $semesterSiswaRecords = SemesterSiswa::where('semester_id', $semester->id)
            ->with('siswa')
            ->get();

        foreach ($semesterSiswaRecords as $ss) {
            $siswa = $ss->siswa;
            if (!$siswa) continue;

            $snapR2 = RekapR2Akhir::where('semester_id', $semester->id)
                ->where('siswa_id', $siswa->id)->first();
            $snapJurnal = RekapJurnalSemester::where('semester_id', $semester->id)
                ->where('siswa_id', $siswa->id)->first();
            $snapMunaqosyah = RekapMunaqosyahSemester::where('semester_id', $semester->id)
                ->where('siswa_id', $siswa->id)->first();
            $snapRiwayat = RekapRiwayatSemester::where('semester_id', $semester->id)
                ->where('siswa_id', $siswa->id)->first();

            $r2Akhir = $snapR2?->r2_akhir ?? 0;
            $totalHari = $snapJurnal?->total_hari ?? 0;
            $kelasRegulerNama = $snapRiwayat?->kelasReguler?->nama ?? $siswa->kelasReguler?->nama ?? 'Tanpa Kelas Reguler';
            $kelasTartilNama = $snapRiwayat?->kelasTartil?->nama ?? $siswa->kelasTartil?->nama ?? '-';

            $item = [
                'siswa' => $siswa,
                'kelasTartil' => $kelasTartilNama,
                'kelasReguler' => $kelasRegulerNama,
                'r2Harian' => $snapR2?->r2_harian ?? 0,
                'r2Penilaian' => $snapR2?->r2_penilaian ?? 0,
                'r2Akhir' => $r2Akhir,
                'totalHari' => $totalHari,
                'countB' => $snapJurnal?->count_b ?? 0,
                'countC' => $snapJurnal?->count_c ?? 0,
                'countK' => $snapJurnal?->count_k ?? 0,
                'munaqosyahStatus' => $this->formatMunaqosyahStatus($snapMunaqosyah),
                'pindahTartil' => $snapRiwayat?->jumlah_pindah_tartil ?? 0,
                'pindahReguler' => $snapRiwayat?->jumlah_pindah_reguler ?? 0,
            ];
            $siswaList[] = $item;

            // Kelompokkan per kelas reguler
            if (!isset($siswaPerKelasReguler[$kelasRegulerNama])) {
                $siswaPerKelasReguler[$kelasRegulerNama] = [];
            }
            $siswaPerKelasReguler[$kelasRegulerNama][] = $item;

            $totalR2Akhir += $r2Akhir;
            $totalMengaji += $totalHari;
            $countSiswa++;
        }

        // ════════════════════════════════════════════
        // SECTION 2: MUNAQOSYAH DETAIL
        // ════════════════════════════════════════════
        $munaqosyahList = UjianMunaqosyah::where('semester_id', $semester->id)
            ->with(['pendaftarans.siswa'])
            ->orderBy('tanggal_ujian')
            ->get()
            ->map(function ($u) {
                $total = $u->pendaftarans->count();
                $lulus = $u->pendaftarans->where('status', 'L')->count();
                $tidakLulus = $u->pendaftarans->where('status', 'TL')->count();
                $terdaftar = $u->pendaftarans->where('status', 'T')->count();
                $rataNilai = $u->pendaftarans->whereNotNull('nilai')->avg('nilai');

                return [
                    'ujian' => $u,
                    'total' => $total,
                    'lulus' => $lulus,
                    'tidakLulus' => $tidakLulus,
                    'terdaftar' => $terdaftar,
                    'rataNilai' => $rataNilai ? round($rataNilai, 2) : '-',
                    'peserta' => $u->pendaftarans->map(fn($p) => [
                        'siswa' => $p->siswa,
                        'nilai' => $p->nilai,
                        'status' => $p->status,
                        'catatan' => $p->catatan,
                    ])->values()->toArray(),
                ];
            })
            ->toArray();

        // ════════════════════════════════════════════
        // SECTION 3: PENILAIAN RAPOR DETAIL
        // Dikelompokkan per KELAS TARTIL (jenis kelas)
        // agar indikator sesuai dengan jenis kelasnya
        // ════════════════════════════════════════════
        $penilaianList = PenilaianRaporInternal::where('semester_id', $semester->id)
            ->with(['nilais.siswa.kelasTartil', 'nilais.indikator'])
            ->orderBy('created_at')
            ->get()
            ->map(function ($p) {
                // Kelompokkan nilai per siswa dulu
                $nilaiPerSiswa = $p->nilais->groupBy('siswa_id')->map(function ($nilais) {
                    $first = $nilais->first();
                    // Ambil jenis kelas tartil siswa
                    $jenisKelas = $first->siswa?->kelasTartil?->jenis ?? 'Lainnya';
                    return [
                        'siswa' => $first->siswa,
                        'jenisKelas' => $jenisKelas,
                        'nilaiRata' => round($nilais->avg('nilai'), 2),
                        'detail' => $nilais->map(fn($n) => [
                            'indikator' => $n->indikator?->nama_indikator ?? '-',
                            'nilai' => $n->nilai,
                        ])->values()->toArray(),
                    ];
                });

                // Kelompokkan lagi per jenis kelas tartil
                $perKelasTartil = $nilaiPerSiswa->groupBy('jenisKelas')->map(function ($siswaList, $jenisKelas) {
                    // Ambil indikator unik untuk kelas ini
                    $indikatorNames = collect();
                    foreach ($siswaList as $s) {
                        foreach ($s['detail'] as $d) {
                            if ($d['indikator'] !== '-') {
                                $indikatorNames->push($d['indikator']);
                            }
                        }
                    }
                    $indikatorNames = $indikatorNames->unique()->values()->toArray();

                    return [
                        'jenisKelas' => $jenisKelas,
                        'totalSiswa' => $siswaList->count(),
                        'indikatorNames' => $indikatorNames,
                        'nilaiPerSiswa' => $siswaList->values()->toArray(),
                    ];
                })->values()->toArray();

                return [
                    'penilaian' => $p,
                    'totalSiswa' => $nilaiPerSiswa->count(),
                    'perKelasTartil' => $perKelasTartil,
                ];
            })
            ->toArray();

        return [
            'semester' => $semester,
            'totalSiswa' => $countSiswa,
            'rataR2Akhir' => $countSiswa > 0 ? round($totalR2Akhir / $countSiswa, 2) : 0,
            'rataMengaji' => $countSiswa > 0 ? round($totalMengaji / $countSiswa, 2) : 0,
            'siswaList' => $siswaList,
            'siswaPerKelasReguler' => $siswaPerKelasReguler,
            'munaqosyahList' => $munaqosyahList,
            'penilaianList' => $penilaianList,
        ];
    }

    private function formatMunaqosyahStatus(?RekapMunaqosyahSemester $snap): string
    {
        if (!$snap || $snap->total_ujian == 0) return 'Tidak mengikuti';
        return "{$snap->total_lulus}/{$snap->total_ujian} Lulus";
    }

    // ═══════════════════════════════════════════════════════════
    // STATISTIK DASHBOARD — Grafik Perkembangan 3 TA ke Belakang
    // ═══════════════════════════════════════════════════════════

    /**
     * Tampilkan dashboard statistik dengan grafik Chart.js.
     */
    public function statistikDashboard(Request $request)
    {
        $chartData = $this->buildStatistikData();
        return view('admin.statistik.index', compact('chartData'));
    }

    /**
     * API endpoint untuk data statistik (JSON).
     */
    public function statistikData(Request $request)
    {
        return response()->json($this->buildStatistikData());
    }

    /**
     * Build data statistik untuk 3 TA: TA aktif + 2 TA sebelumnya.
     * Data dipisah per TA (bukan total 3 TA).
     * Jika TA aktif = 2025/2026, maka tampilkan: 2023/2024, 2024/2025, 2025/2026
     */
    private function buildStatistikData(): array
    {
        // Ambil TA terbaru
        $taTerbaru = TahunAjaran::orderBy('nama', 'desc')->first();
        if (!$taTerbaru) {
            $taTerbaru = Semester::select('tahun_ajaran')->distinct()->orderBy('tahun_ajaran', 'desc')->first();
        }
        $taNama = $taTerbaru ? ($taTerbaru->nama ?? $taTerbaru->tahun_ajaran) : null;

        if (!$taNama) {
            return ['taLabels' => [], 'semesterLabels' => [], 'siswa' => [], 'r2Akhir' => [], 'r2Harian' => [], 'r2Penilaian' => [], 'jurnalHari' => [], 'perTA' => [], 'munaqosyah' => [], 'tahfidz' => []];
        }

        // Generate 3 TA: (tahun-2)/(tahun-1), (tahun-1)/tahun, tahun/(tahun+1)
        $tahunParts = explode('/', $taNama);
        $tahunMulai = (int) ($tahunParts[0] ?? date('Y'));
        $taList = [];
        for ($i = 2; $i >= 0; $i--) {
            $t1 = $tahunMulai - $i;
            $t2 = $t1 + 1;
            $taList[] = "{$t1}/{$t2}";
        }

        $data = [
            'taLabels' => $taList,
            'semesterLabels' => [],
            'siswa' => [],
            'r2Akhir' => [],
            'r2Harian' => [],
            'r2Penilaian' => [],
            'jurnalHari' => [],
            // Data per TA (untuk ringkasan card dan munaqosyah per TA)
            'perTA' => [],
            'munaqosyah' => [],
        ];

        // ══════ DATA PER SEMESTER (untuk grafik line/bar) ══════
        foreach ($taList as $ta) {
            $semesters = Semester::where('tahun_ajaran', $ta)->orderBy('tanggal_mulai', 'asc')->get()->keyBy('jenis');
            foreach (['ganjil', 'genap'] as $jenis) {
                $label = $jenis === 'ganjil' ? 'Ganjil' : 'Genap';
                $data['semesterLabels'][] = "{$ta} {$label}";
                $sem = $semesters->get($jenis);
                if ($sem) {
                    $data['siswa'][] = SemesterSiswa::where('semester_id', $sem->id)->count();
                    $r2 = RekapR2Akhir::where('semester_id', $sem->id);
                    $data['r2Akhir'][] = round($r2->avg('r2_akhir') ?? 0, 2);
                    $data['r2Harian'][] = round($r2->avg('r2_harian') ?? 0, 2);
                    $data['r2Penilaian'][] = round($r2->avg('r2_penilaian') ?? 0, 2);
                    $data['jurnalHari'][] = round(RekapJurnalSemester::where('semester_id', $sem->id)->avg('total_hari') ?? 0, 1);
                } else {
                    $data['siswa'][] = 0;
                    $data['r2Akhir'][] = 0;
                    $data['r2Harian'][] = 0;
                    $data['r2Penilaian'][] = 0;
                    $data['jurnalHari'][] = 0;
                }
            }
        }

        // ══════ DATA PER TA (untuk ringkasan dan munaqosyah) ══════
        foreach ($taList as $ta) {
            $semIds = Semester::where('tahun_ajaran', $ta)->pluck('id')->toArray();
            $totalSiswa = SemesterSiswa::whereIn('semester_id', $semIds)->distinct('siswa_id')->count('siswa_id');
            $r2Avg = RekapR2Akhir::whereIn('semester_id', $semIds)->avg('r2_akhir');
            $jurnalAvg = RekapJurnalSemester::whereIn('semester_id', $semIds)->avg('total_hari');
            $totalSemester = count($semIds);
            $totalMunaqosyah = UjianMunaqosyah::whereIn('semester_id', $semIds)->count();
            $totalPenilaian = PenilaianRaporInternal::whereIn('semester_id', $semIds)->count();

            $data['perTA'][$ta] = [
                'totalSiswa' => $totalSiswa,
                'rataR2Akhir' => $r2Avg ? round($r2Avg, 2) : 0,
                'rataMengaji' => $jurnalAvg ? round($jurnalAvg, 1) : 0,
                'totalSemester' => $totalSemester,
                'totalMunaqosyah' => $totalMunaqosyah,
                'totalPenilaian' => $totalPenilaian,
            ];

            // Munaqosyah per TA per tingkat + detail per ujian
            foreach (['unit', 'yayasan', 'pesantren'] as $tingkat) {
                $ujianList = UjianMunaqosyah::whereIn('semester_id', $semIds)
                    ->where('tingkat', $tingkat)
                    ->withCount(['pendaftarans'])
                    ->orderBy('tanggal_ujian', 'asc')
                    ->get();

                $ujianIds = $ujianList->pluck('id')->toArray();
                $totalPeserta = !empty($ujianIds) ? MunaqosyahPendaftaran::whereIn('munaqosyah_id', $ujianIds)->count() : 0;
                $totalLulus = !empty($ujianIds) ? MunaqosyahPendaftaran::whereIn('munaqosyah_id', $ujianIds)->where('status', 'L')->count() : 0;
                $totalTidakLulus = !empty($ujianIds) ? MunaqosyahPendaftaran::whereIn('munaqosyah_id', $ujianIds)->where('status', 'TL')->count() : 0;

                // Detail per ujian individual
                $detailUjian = $ujianList->map(function ($u) {
                    $lulus = $u->pendaftarans()->where('status', 'L')->count();
                    $tidakLulus = $u->pendaftarans()->where('status', 'TL')->count();
                    $total = $u->pendaftarans_count;
                    return [
                        'id' => $u->id,
                        'nama' => $u->nama,
                        'tanggal' => $u->tanggal_ujian?->format('d/m/Y') ?? '-',
                        'status' => $u->status,
                        'total' => $total,
                        'lulus' => $lulus,
                        'tidakLulus' => $tidakLulus,
                        'persentaseLulus' => $total > 0 ? round(($lulus / $total) * 100, 1) : 0,
                    ];
                })->values()->toArray();

                if (!isset($data['munaqosyah'][$ta])) {
                    $data['munaqosyah'][$ta] = [];
                }
                $data['munaqosyah'][$ta][$tingkat] = [
                    'label' => match($tingkat) {
                        'unit' => 'Munaqosyah Unit',
                        'yayasan' => 'Munaqosyah Yayasan',
                        'pesantren' => 'Munaqosyah Pesantren',
                        default => $tingkat,
                    },
                    'total' => $totalPeserta,
                    'lulus' => $totalLulus,
                    'tidakLulus' => $totalTidakLulus,
                    'persentaseLulus' => $totalPeserta > 0 ? round(($totalLulus / $totalPeserta) * 100, 1) : 0,
                    'jumlahUjian' => count($detailUjian),
                    'detailUjian' => $detailUjian,
                ];
            }
        }

        // ══════ DATA TAHFIDZ ══════
        $data['tahfidz'] = \App\Http\Controllers\TahfidzController::buildTahfidzData();

        return $data;
    }
}
