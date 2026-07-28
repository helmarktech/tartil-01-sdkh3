<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KelasReguler;
use App\Models\Siswa;
use App\Models\Semester;
use App\Models\KenaikanKelasReguler;
use Illuminate\Support\Facades\DB;

class KenaikanKelasController extends Controller
{
    // ==================== ADMIN: INDEX ====================
    public function index()
    {
        // Info cards: hitung siswa per jenjang
        $countKelas6 = Siswa::whereHas('kelasReguler', fn($q) => $q->where('jenjang', 6))->where('status', 'aktif')->count();
        $countKelas1to5 = Siswa::whereHas('kelasReguler', fn($q) => $q->whereIn('jenjang', [1,2,3,4,5]))->where('status', 'aktif')->count();
        $countSemGanjilAktif = Semester::where('jenis', 'ganjil')->where('status', 'aktif')->count();

        // Status validasi
        $semesterGanjil = Semester::where('jenis', 'ganjil')->where('status', 'aktif')->first();
        $semesterAktifLama = Semester::where('is_aktif', true)->where('jenis', 'genap')->first();
        $sudahNaik = $semesterGanjil ? KenaikanKelasReguler::where('tahun_ajaran', $semesterGanjil->tahun_ajaran)->exists() : false;

        // Daftar siswa aktif untuk mutasi
        $siswaAktifList = Siswa::where('status', 'aktif')->with('kelasReguler')->orderBy('nama')->get();

        // Riwayat kenaikan
        $riwayat = KenaikanKelasReguler::with(['siswa', 'kelasLama', 'kelasBaru', 'semester', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return view('admin.kenaikan.index', compact(
            'countKelas6', 'countKelas1to5', 'countSemGanjilAktif',
            'semesterGanjil', 'semesterAktifLama', 'sudahNaik',
            'siswaAktifList', 'riwayat'
        ));
    }

    // ==================== PROSES KENAIKAN OTOMATIS ====================
    public function prosesMassal(Request $request)
    {
        $request->validate([
            'tahun_ajaran_baru' => 'required|max:20',
            'semester_ganjil_id' => 'required|exists:semesters,id',
        ]);

        $semesterGanjil = Semester::findOrFail($request->semester_ganjil_id);

        // ===== VALIDASI KEAMANAN =====

        // 1. Semester harus ganjil (awal TA)
        if ($semesterGanjil->jenis !== 'ganjil') {
            return back()->with('error', 'Kenaikan kelas hanya boleh dilakukan pada semester ganjil (awal tahun ajaran).');
        }

        // 2. TA baru harus berbeda dari TA sebelumnya
        $taLama = Semester::buka()
            ->where('id', '!=', $semesterGanjil->id)
            ->orderBy('tanggal_mulai', 'desc')
            ->first();

        if ($taLama && $taLama->tahun_ajaran === $semesterGanjil->tahun_ajaran) {
            return back()->with('error', 'Kenaikan kelas hanya boleh dilakukan saat memasuki tahun ajaran BARU.');
        }

        // 3. Cek sudah pernah naik untuk TA ini
        $sudahNaik = KenaikanKelasReguler::where('tahun_ajaran', $semesterGanjil->tahun_ajaran)
            ->where('semester_id', $semesterGanjil->id)
            ->exists();
        if ($sudahNaik) {
            return back()->with('error', 'Kenaikan kelas untuk TA ' . $semesterGanjil->tahun_ajaran . ' sudah pernah dilakukan.');
        }

        // 4. TA lama harus sudah ditutup (atau minimal tidak ada semester aktif di TA lama)
        $semesterAktifLama = Semester::aktif()
            ->where('id', '!=', $semesterGanjil->id)
            ->first();
        if ($semesterAktifLama) {
            return back()->with('error', 'Semester aktif TA sebelumnya (' . $semesterAktifLama->nama . ') harus dinonaktifkan/ditutup terlebih dahulu.');
        }

        // === VALIDASI: Kelas aktif jenjang 1-5 yang punya siswa harus punya kelas tujuan ===
        $kelasTanpaTujuan = [];
        for ($jenjang = 5; $jenjang >= 1; $jenjang--) {
            $kelasLamaList = KelasReguler::where('jenjang', $jenjang)
                ->where('is_aktif', true)
                ->get();
            foreach ($kelasLamaList as $kl) {
                // Hanya validasi kelas yang punya siswa aktif
                $jumlahSiswa = Siswa::where('kelas_reguler_id', $kl->id)->where('status', 'aktif')->count();
                if ($jumlahSiswa === 0) continue; // kelas kosong, skip validasi

                // Cek kelas tujuan: jenjang+1, rombel sama
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
            return back()->with('error', 'Kenaikan kelas dibatalkan. Kelas tujuan tidak ditemukan untuk: ' . $daftar . '. Silakan buat kelas tujuan terlebih dahulu dengan jenjang dan rombel yang sesuai.');
        }

        return DB::transaction(function () use ($semesterGanjil, $request) {
            $log = [];
            $countNaik = 0;
            $countLulus = 0;
            $countMutasi = 0;

            // === PROSES 1: KELAS 6 → LULUS (semua) ===
            $kelas6 = KelasReguler::where('jenjang', 6)->where('is_aktif', true)->get();
            foreach ($kelas6 as $k6) {
                $siswaKelas6 = Siswa::where('kelas_reguler_id', $k6->id)
                    ->where('status', 'aktif')
                    ->get();

                foreach ($siswaKelas6 as $s) {
                    // Catat riwayat kenaikan (lulus)
                    KenaikanKelasReguler::create([
                        'siswa_id' => $s->id,
                        'kelas_reguler_lama_id' => $k6->id,
                        'kelas_reguler_baru_id' => null,
                        'semester_id' => $semesterGanjil->id,
                        'tahun_ajaran' => $semesterGanjil->tahun_ajaran,
                        'kategori' => 'lulus',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                        'keterangan' => 'Lulus jenjang 6, TA ' . $semesterGanjil->tahun_ajaran,
                    ]);

                    // Update status siswa
                    $s->update([
                        'status' => 'lulus',
                        'keterangan_status' => 'Lulus jenjang 6',
                        'kelas_reguler_id' => null,
                        'kelas_tartil_id' => null,
                    ]);
                    $countLulus++;
                }
            }

            // === PROSES 2: KELAS 1-5 → NAIK (rombel sama) ===
            for ($jenjang = 5; $jenjang >= 1; $jenjang--) {
                $kelasLamaList = KelasReguler::where('jenjang', $jenjang)
                    ->where('is_aktif', true)
                    ->get();

                foreach ($kelasLamaList as $kl) {
                    // Cari kelas baru: jenjang+1, rombel sama (sudah divalidasi, pasti ada)
                    $kb = KelasReguler::where('jenjang', $jenjang + 1)
                        ->where('tingkat', $kl->tingkat)
                        ->where('is_aktif', true)
                        ->first();

                    $siswaList = Siswa::where('kelas_reguler_id', $kl->id)
                        ->where('status', 'aktif')
                        ->get();

                    foreach ($siswaList as $s) {
                        // Catat riwayat
                        KenaikanKelasReguler::create([
                            'siswa_id' => $s->id,
                            'kelas_reguler_lama_id' => $kl->id,
                            'kelas_reguler_baru_id' => $kb->id,
                            'semester_id' => $semesterGanjil->id,
                            'tahun_ajaran' => $semesterGanjil->tahun_ajaran,
                            'kategori' => 'naik',
                            'approved_by' => auth()->id(),
                            'approved_at' => now(),
                            'keterangan' => "Naik dari jenjang {$jenjang} ke " . ($jenjang + 1) . " (rombel {$kl->tingkat})",
                        ]);

                        // Update kelas siswa
                        $s->update([
                            'kelas_reguler_id' => $kb->id,
                        ]);
                        $countNaik++;
                    }
                }
            }

            // === PROSES 3: SISWA NONAKTIF (mutasi_keluar) tetap mutasi, tidak ikut naik ===
            // Sudah ditangani otomatis karena hanya siswa aktif yang diproses

            // === PROSES 4: Snapshot ke semester_siswa ===
            $this->snapshotSemester($semesterGanjil);

            $msg = "Kenaikan kelas TA {$semesterGanjil->tahun_ajaran} berhasil. ";
            $msg .= "Lulus: {$countLulus}, Naik: {$countNaik}.";
            return redirect()->route('admin.kenaikan.index')->with('success', $msg);
        });
    }

    // ==================== PROSES MUTASI KELUAR ====================
    public function prosesMutasi(Request $request)
    {
        $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'exists:siswas,id',
            'keterangan' => 'required|string|max:255',
        ]);

        $semester = Semester::aktif()->first();
        if (!$semester) {
            return back()->with('error', 'Tidak ada semester aktif.');
        }

        return DB::transaction(function () use ($request, $semester) {
            foreach ($request->siswa_ids as $siswaId) {
                $s = Siswa::find($siswaId);
                if ($s->status !== 'aktif') continue;

                KenaikanKelasReguler::create([
                    'siswa_id' => $s->id,
                    'kelas_reguler_lama_id' => $s->kelas_reguler_id,
                    'kelas_reguler_baru_id' => null,
                    'semester_id' => $semester->id,
                    'tahun_ajaran' => $semester->tahun_ajaran,
                    'kategori' => 'mutasi',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'keterangan' => $request->keterangan,
                ]);

                $s->update([
                    'status' => 'mutasi_keluar',
                    'keterangan_status' => $request->keterangan,
                    'kelas_reguler_id' => null,
                    'kelas_tartil_id' => null,
                ]);
            }

            return back()->with('success', count($request->siswa_ids) . ' siswa dimutasi keluar.');
        });
    }

    // ==================== SNAPSHOT SEMESTER ====================
    private function snapshotSemester(Semester $semester)
    {
        $siswaAktif = Siswa::where('status', 'aktif')->get();
        foreach ($siswaAktif as $s) {
            \App\Models\SemesterSiswa::firstOrCreate(
                ['semester_id' => $semester->id, 'siswa_id' => $s->id],
                [
                    'kelas_id' => $s->kelas_tartil_id,
                    'kelas_reguler_id' => $s->kelas_reguler_id,
                    'status_siswa' => 'aktif',
                ]
            );
        }

        // Kelas tartil snapshot
        $kelasTartil = \App\Models\Kelas::where('status', 'aktif')->get();
        foreach ($kelasTartil as $k) {
            $jumlah = Siswa::where('kelas_tartil_id', $k->id)->where('status', 'aktif')->count();
            \App\Models\SemesterKelas::firstOrCreate(
                ['semester_id' => $semester->id, 'kelas_id' => $k->id],
                ['jumlah_siswa' => $jumlah, 'keterangan' => 'Snapshot awal semester']
            );
        }
    }
}
