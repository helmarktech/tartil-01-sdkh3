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
use Illuminate\Support\Facades\Hash;

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

        $recentPerpindahan = PerpindahanKelas::with(['siswa', 'kelasLama', 'kelasBaru'])
            ->orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'recentPerpindahan'));
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

        $guru->update($validated);
        return redirect()->route('admin.guru.index')->with('success', 'Data guru diperbarui.');
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
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable',
            'nama_ortu' => 'nullable',
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
        ]);

        // Default nilai untuk field yang tidak ditampilkan di form
        $validated['mata_pelajaran'] = $validated['jenis'];
        $validated['hari'] = '-';
        $validated['jam_mulai'] = '00:00';
        $validated['jam_selesai'] = '00:00';

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
            ->orWhereDoesntHave('approval')
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        $kelasTartilIds = auth()->user()->isAdmin()
            ? null
            : Kelas::where('guru_id', auth()->user()->guru->id ?? 0)->pluck('id');

        return view('admin.munaqosyah.approval', compact('pendaftarans', 'kelasTartilIds'));
    }

    public function munaqosyahApprovalSetuju(MunaqosyahApproval $approval)
    {
        $approval->update([
            'status' => 'disetujui',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Pendaftaran siswa disetujui.');
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
            'nama' => 'required|max:50',
            'jenjang' => 'required|integer|min:1|max:6',
            'tingkat' => 'required|string|max:20',
            'guru_pengampu_id' => 'nullable|exists:guru_regulers,id',
            'keterangan' => 'nullable|string|max:255',
        ]);
        $validated['is_aktif'] = true;

        KelasReguler::create($validated);
        return redirect()->route('admin.kelas-reguler.daftar')->with('success', 'Kelas reguler berhasil ditambahkan.');
    }

    public function kelasRegulerUpdate(Request $request, KelasReguler $kelasReguler)
    {
        $validated = $request->validate([
            'nama' => 'required|max:50',
            'jenjang' => 'required|integer|min:1|max:6',
            'tingkat' => 'required|string|max:20',
            'guru_pengampu_id' => 'nullable|exists:guru_regulers,id',
            'keterangan' => 'nullable|string|max:255',
        ]);
        $validated['is_aktif'] = $request->has('is_aktif');

        $kelasReguler->update($validated);
        return redirect()->route('admin.kelas-reguler.daftar')->with('success', 'Kelas reguler diperbarui.');
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

    // ============ REKAP KELAS TARTIL SEMESTER AKTIF ============
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

        // Ambil kelas tartil aktif dengan jumlah siswa
        $kelasList = Kelas::where('status', 'aktif')
            ->with('guru')
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
        ]);
        $validated['is_aktif'] = true;
        GuruReguler::create($validated);
        return redirect()->route('admin.guru-reguler.index')->with('success', 'Guru reguler berhasil ditambahkan.');
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
        ]);
        $guruReguler->update($validated);
        return redirect()->route('admin.guru-reguler.index')->with('success', 'Data guru reguler diperbarui.');
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

        // 2. Cek apakah ada TA aktif yang belum ditutup
        $taAktif = TahunAjaran::aktif()->first();
        if ($taAktif) {
            return back()->with('error', 'TA "' . $taAktif->nama . '" masih aktif. Tutup TA lama terlebih dahulu sebelum membuat TA baru.');
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
                        $s->update(['kelas_reguler_id' => $kb->id]);
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
        $semester->update(['status' => 'ditutup', 'is_aktif' => false]);

        // Jika semua semester di TA sudah ditutup, tutup TA juga
        if ($semester->tahun_ajaran_id) {
            $allClosed = Semester::where('tahun_ajaran_id', $semester->tahun_ajaran_id)
                ->where('status', '!=', 'ditutup')
                ->doesntExist();
            if ($allClosed) {
                TahunAjaran::where('id', $semester->tahun_ajaran_id)->update(['status' => 'ditutup']);
            }
        }

        return back()->with('success', 'Semester ' . $semester->nama . ' ditutup.');
    }
}
