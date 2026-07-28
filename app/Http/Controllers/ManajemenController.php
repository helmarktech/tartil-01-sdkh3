<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use App\Models\RiwayatMutasi;
use App\Models\KelasReguler;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\SemesterSiswa;
use App\Models\SemesterKelas;

class ManajemenController extends Controller
{
    // ==================== MANAJEMEN GURU ====================
    public function guruIndex(Request $request)
    {
        $query = Guru::withTrashed()->orderBy('nama');
        if ($request->filled('search')) {
            $query->where('nama', 'like', "%{$request->search}%");
        }
        $gurus = $query->paginate(20);
        return view('admin.manajemen.guru', compact('gurus'));
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
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'nip.unique' => 'NIP sudah digunakan guru lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan guru lain.',
            'no_hp.required' => 'No HP wajib diisi.',
            'no_hp.max' => 'No HP maksimal 15 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
        ]);

        $guru = Guru::create($validated + ['is_aktif' => true]);
        $password = $request->filled('password') ? $request->password : '123456';
        User::create([
            'nama' => $guru->nama,
            'email' => $guru->email,
            'password' => Hash::make($password),
            'role' => 'guru',
            'guru_id' => $guru->id,
        ]);
        return back()->with('success', "Guru {$guru->nama} ditambahkan. Password: {$password}");
    }

    public function guruEdit(Guru $guru)
    {
        return view('admin.manajemen.guru-edit', compact('guru'));
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
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.max' => 'Nama maksimal 100 karakter.',
            'nip.unique' => 'NIP sudah digunakan guru lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan guru lain.',
            'no_hp.required' => 'No HP wajib diisi.',
            'no_hp.max' => 'No HP maksimal 15 karakter.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin harus Laki-laki atau Perempuan.',
        ]);
        $guru->update($validated);
        $guru->user()->update(['email' => $request->email, 'nama' => $request->nama]);
        return back()->with('success', 'Data guru "' . $validated['nama'] . '" berhasil diperbarui.');
    }

    public function guruResetPassword(Request $request, Guru $guru)
    {
        $request->validate(['password' => 'required|min:6']);
        $guru->user()->update(['password' => Hash::make($request->password)]);
        return back()->with('success', "Password {$guru->nama} direset.");
    }

    public function guruNonaktifkan(Request $request, Guru $guru)
    {
        $request->validate(['keterangan' => 'required']);
        $guru->update(['is_aktif' => false]);
        $guru->user()->update(['is_aktif' => false]);
        RiwayatMutasi::create([
            'mutasi_type' => Guru::class, 'mutasi_id' => $guru->id,
            'jenis' => 'nonaktifkan', 'keterangan' => $request->keterangan,
            'dilakukan_oleh' => auth()->id(), 'tanggal_mutasi' => now(),
        ]);
        return back()->with('success', "Guru {$guru->nama} dinonaktifkan.");
    }

    public function guruHapus(Request $request, Guru $guru)
    {
        $request->validate(['keterangan' => 'required']);
        RiwayatMutasi::create([
            'mutasi_type' => Guru::class, 'mutasi_id' => $guru->id,
            'jenis' => 'hapus', 'keterangan' => $request->keterangan,
            'dilakukan_oleh' => auth()->id(), 'tanggal_mutasi' => now(),
        ]);
        $guru->delete();
        return back()->with('success', "Guru {$guru->nama} dihapus (soft delete).");
    }

    public function guruAktifkan(Guru $guru)
    {
        $guru->restore();
        $guru->update(['is_aktif' => true]);
        RiwayatMutasi::create([
            'mutasi_type' => Guru::class, 'mutasi_id' => $guru->id,
            'jenis' => 'aktifkan', 'keterangan' => 'Diaktifkan kembali',
            'dilakukan_oleh' => auth()->id(), 'tanggal_mutasi' => now(),
        ]);
        return back()->with('success', "Guru {$guru->nama} diaktifkan.");
    }

    // ==================== MANAJEMEN SISWA ====================
    public function siswaIndex(Request $request)
    {
        $tab = $request->get('tab', 'aktif'); // aktif | nonaktif

        if ($tab === 'nonaktif') {
            // Tab non-aktif: SEMUA siswa yang statusnya bukan aktif
            $query = Siswa::withTrashed()
                ->with(['kelasReguler', 'kelasTartil', 'riwayatMutasi' => fn($q) => $q->limit(1)])
                ->where('status', '!=', 'aktif');
        } else {
            // Tab aktif: siswa aktif
            $query = Siswa::with(['kelasReguler', 'kelasTartil'])->where('status', 'aktif');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('kelas_reguler')) {
            $query->where('kelas_reguler_id', $request->kelas_reguler);
        }
        $siswas = $query->orderBy('nama')->paginate(20)->withQueryString();
        $kelasRegulars = KelasReguler::where('is_aktif', true)->get();

        // Hitung counter
        $countAktif = Siswa::where('status', 'aktif')->count();
        $countNonaktif = Siswa::withTrashed()->where('status', '!=', 'aktif')->count();

        // Semester aktif info untuk view (hindari query DB dari Blade)
        $semesterAktif = Semester::aktif()->first();
        // Semua semester untuk matching tanggal mutasi di tab non-aktif
        // Hanya select kolom yang benar-benar ada di DB (nama adalah accessor)
        $semesters = Semester::orderBy('tanggal_mulai')->get(['id', 'tahun_ajaran', 'jenis', 'tanggal_mulai', 'tanggal_selesai']);

        return view('admin.manajemen.siswa', compact('siswas', 'kelasRegulars', 'tab', 'countAktif', 'countNonaktif', 'semesterAktif', 'semesters'));
    }

    public function siswaEdit(Siswa $siswa)
    {
        $kelasRegulars = KelasReguler::where('is_aktif', true)->orderBy('nama')->get();
        $kelasTartils = Kelas::where('status', 'aktif')->orderByRaw("FIELD(jenis, 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz')" )->orderBy('nama')->get();
        return view('admin.manajemen.siswa-edit', compact('siswa', 'kelasRegulars', 'kelasTartils'));
    }

    public function siswaStore(Request $request)
    {
        // VALIDASI: Wajib ada semester aktif sebelum menambahkan siswa
        $semesterAktif = Semester::aktif()->first();
        if (!$semesterAktif) {
            return back()->with('error', 'Tidak dapat menambahkan siswa. Tidak ada semester aktif. Silakan buat Tahun Ajaran dan aktifkan semester terlebih dahulu.')
                ->withInput();
        }

        $validated = $request->validate([
            'nis' => 'required|unique:siswas,nis',
            'nama' => 'required|max:100',
            'no_hp' => 'required|max:15',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_reguler_id' => 'required|exists:kelas_regulers,id',
            'kelas_tartil_id' => 'nullable|exists:kelas,id',
            'tanggal_masuk' => 'required|date',
            'tempat_lahir' => 'nullable|max:100',
            'tanggal_lahir' => 'nullable|date',
            'nama_ayah' => 'nullable|max:100',
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan siswa lain.',
            'nama.required' => 'Nama wajib diisi.',
            'no_hp.required' => 'No HP wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'kelas_reguler_id.required' => 'Kelas reguler wajib dipilih.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
        ]);
        $validated['password'] = Hash::make($request->nis);
        $siswa = Siswa::create($validated);

        // Auto-registrasi siswa ke semester aktif saat ini
        SemesterSiswa::create([
            'semester_id' => $semesterAktif->id,
            'siswa_id' => $siswa->id,
            'kelas_id' => $siswa->kelas_tartil_id,
            'kelas_reguler_id' => $siswa->kelas_reguler_id,
            'status_siswa' => 'aktif',
            'keterangan' => 'Siswa baru daftar',
        ]);

        // Update jumlah siswa di semester_kelas jika siswa masuk ke kelas tartil
        // Pakai raw UPDATE untuk atomic operation (hindari race condition)
        if ($siswa->kelas_tartil_id) {
            SemesterKelas::firstOrCreate(
                ['semester_id' => $semesterAktif->id, 'kelas_id' => $siswa->kelas_tartil_id],
                ['jumlah_siswa' => 0, 'keterangan' => 'Kelas aktif']
            );
            \DB::table('semester_kelas')
                ->where('semester_id', $semesterAktif->id)
                ->where('kelas_id', $siswa->kelas_tartil_id)
                ->update(['jumlah_siswa' => \DB::raw('jumlah_siswa + 1'), 'updated_at' => now()]);
        }

        return back()->with('success', 'Siswa berhasil ditambahkan. Terdaftar di semester ' . $semesterAktif->nama . '.');
    }

    public function siswaUpdate(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:30|unique:siswas,nis,' . $siswa->id,
            'nama' => 'required|max:100',
            'no_hp' => 'required|max:15',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_reguler_id' => 'required|exists:kelas_regulers,id',
            'kelas_tartil_id' => 'nullable|exists:kelas,id',
            'tanggal_masuk' => 'required|date',
            'tempat_lahir' => 'nullable|max:100',
            'tanggal_lahir' => 'nullable|date',
            'nama_ayah' => 'nullable|max:100',
        ], [
            'nis.required' => 'NIS wajib diisi.',
            'nis.unique' => 'NIS sudah digunakan siswa lain.',
            'nama.required' => 'Nama wajib diisi.',
            'no_hp.required' => 'No HP wajib diisi.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'kelas_reguler_id.required' => 'Kelas reguler wajib dipilih.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'tanggal_masuk.date' => 'Tanggal masuk harus format tanggal yang valid.',
            'tanggal_lahir.date' => 'Tanggal lahir harus format tanggal yang valid.',
        ]);

        // Tangani tanggal kosong dari input HTML date (kirim string kosong) → konversi ke null
        if (empty($validated['tanggal_lahir'])) {
            $validated['tanggal_lahir'] = null;
        }

        $dataUpdate = $validated;
        $nisLama = $siswa->nis;
        $nisBerubah = $validated['nis'] !== $nisLama;

        // Jika NIS berubah dan user meminta update password
        if ($request->boolean('update_password_nis') && $nisBerubah) {
            $dataUpdate['password'] = Hash::make($validated['nis']);
        }

        $siswa->update($dataUpdate);

        $pesanPassword = ($request->boolean('update_password_nis') && $nisBerubah)
            ? ' Password login diperbarui sesuai NIS baru.'
            : '';

        return redirect()->route('admin.manajemen.siswa')
            ->with('success', 'Data siswa "' . $validated['nama'] . '" (NIS: ' . $validated['nis'] . ') berhasil diperbarui.' . $pesanPassword);
    }

    public function siswaResetPassword(Request $request, Siswa $siswa)
    {
        $request->validate(['password' => 'required|min:6']);
        $siswa->update(['password' => Hash::make($request->password)]);
        return back()->with('success', "Password {$siswa->nama} direset.");
    }

    public function siswaNonaktifkan(Request $request, Siswa $siswa)
    {
        $request->validate(['keterangan' => 'required']);
        $siswa->update([
            'status' => 'mutasi_keluar',
            'keterangan_status' => $request->keterangan,
            'kelas_reguler_id' => null,
            'kelas_tartil_id' => null,
        ]);
        RiwayatMutasi::create([
            'mutasi_type' => Siswa::class, 'mutasi_id' => $siswa->id,
            'jenis' => 'mutasi_keluar', 'keterangan' => $request->keterangan,
            'dilakukan_oleh' => auth()->id(), 'tanggal_mutasi' => now(),
        ]);
        return back()->with('success', "Siswa {$siswa->nama} dimutasi keluar.");
    }

    public function siswaHapus(Request $request, Siswa $siswa)
    {
        $request->validate(['keterangan' => 'required']);

        // Cek apakah siswa sudah punya nilai rapor internal
        $punyaNilaiRapor = \App\Models\PenilaianRaporNilai::where('siswa_id', $siswa->id)->exists();
        if ($punyaNilaiRapor) {
            return back()->with('error', "Siswa {$siswa->nama} tidak dapat dihapus karena sudah memiliki nilai rapor internal.");
        }

        RiwayatMutasi::create([
            'mutasi_type' => Siswa::class, 'mutasi_id' => $siswa->id,
            'jenis' => 'hapus', 'keterangan' => $request->keterangan,
            'dilakukan_oleh' => auth()->id(), 'tanggal_mutasi' => now(),
        ]);
        $siswa->delete();
        return back()->with('success', "Siswa {$siswa->nama} dihapus.");
    }

    public function siswaAktifkan(Siswa $siswa)
    {
        $siswa->restore();
        $siswa->update(['status' => 'aktif']);
        RiwayatMutasi::create([
            'mutasi_type' => Siswa::class, 'mutasi_id' => $siswa->id,
            'jenis' => 'aktifkan', 'keterangan' => 'Diaktifkan kembali',
            'dilakukan_oleh' => auth()->id(), 'tanggal_mutasi' => now(),
        ]);
        return back()->with('success', "Siswa {$siswa->nama} diaktifkan.");
    }
}
