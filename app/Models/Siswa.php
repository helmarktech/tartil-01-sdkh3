<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Authenticatable
{
    use SoftDeletes, \App\Traits\Auditable;

    protected $table = 'siswas';
    protected $fillable = [
        'nis', 'nama', 'no_hp', 'password', 'jenis_kelamin',
        'kelas_reguler_id', 'kelas_tartil_id', 'tanggal_masuk_kelas_tartil',
        'keterangan_mutasi', 'tanggal_lahir', 'tempat_lahir', 'alamat',
        'nama_ayah', 'no_hp_ortu', 'tanggal_masuk', 'status', 'keterangan_status'
    ];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_masuk' => 'date',
        'tanggal_masuk_kelas_tartil' => 'date',
        'deleted_at' => 'datetime',
    ];

    public function kelasReguler()
    {
        return $this->belongsTo(KelasReguler::class);
    }

    public function kelasTartil()
    {
        return $this->belongsTo(Kelas::class, 'kelas_tartil_id');
    }

    public function jurnalDetails()
    {
        return $this->hasMany(JurnalDetail::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function perpindahanKelas()
    {
        return $this->hasMany(PerpindahanKelas::class);
    }

    public function trackRecordKelas()
    {
        return $this->perpindahanKelas()
            ->with(['kelasLama', 'kelasBaru', 'semester'])
            ->orderBy('created_at', 'desc');
    }

    // Accessor: jenjang kelas reguler saat ini (1-6 atau null)
    public function getJenjangKelasAttribute(): ?int
    {
        return $this->kelasReguler?->jenjang;
    }

    // Cek apakah siswa bisa di-edit (hanya aktif yang bisa diedit)
    public function getCanEditAttribute(): bool
    {
        return $this->status === 'aktif';
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', strtoupper($this->nama));
        return implode('', array_slice(array_map(fn($w) => $w[0] ?? '', $words), 0, 2));
    }

    // Riwayat semester: semua semester yang pernah diikuti siswa beserta kelasnya
    public function semesters()
    {
        return $this->belongsToMany(Semester::class, 'semester_siswa')
            ->withPivot('kelas_id', 'kelas_reguler_id', 'status_siswa', 'keterangan')
            ->withTimestamps()
            ->orderBy('semesters.tanggal_mulai', 'desc');
    }

    public function semesterRecords()
    {
        return $this->hasMany(SemesterSiswa::class);
    }

    // Get riwayat kelas per semester untuk ditampilkan
    public function riwayatKelasPerSemester()
    {
        return $this->semesterRecords()
            ->with(['semester', 'kelasTartil', 'kelasReguler'])
            ->orderByDesc('created_at');
    }

    // Riwayat mutasi untuk siswa ini (nonaktif/mutasi keluar/hapus)
    public function riwayatMutasi()
    {
        return $this->morphMany(RiwayatMutasi::class, 'mutasi')
            ->with('pelaku')
            ->orderBy('tanggal_mutasi', 'desc');
    }

    // ═══════════════════════════════════════════════
    // SISWA MUTASI (MASUK PERTENGAHAN SEMESTER)
    // ═══════════════════════════════════════════════

    /**
     * Cek apakah siswa ini mutasi masuk pertengahan semester.
     * true = siswa masuk di pertengahan semester (ada tanggal_masuk_kelas_tartil).
     */
    public function getIsMutasiAttribute(): bool
    {
        return $this->tanggal_masuk_kelas_tartil !== null;
    }

    /**
     * Label mutasi untuk tampilan.
     */
    public function getMutasiLabelAttribute(): ?string
    {
        if (!$this->isMutasi) return null;
        return 'Mutasi masuk ' . $this->tanggal_masuk_kelas_tartil->format('d/m/Y');
    }

    /**
     * Hitung target pertemuan dinamis untuk siswa ini.
     * - Siswa mutasi: hari kerja (Senin-Jumat) sejak tanggal masuk sampai akhir semester
     * - Siswa biasa: target default (null = semua hari semester)
     */
    public function getTargetPertemuanDinamis(Semester $semester): ?int
    {
        if (!$this->isMutasi) return null;

        $start = $this->tanggal_masuk_kelas_tartil;
        $end = min($semester->tanggal_selesai, now());

        // Hitung hari kerja (Senin-Jumat) antara start dan end
        $hariKerja = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            if ($current->isWeekday()) $hariKerja++;
            $current->addDay();
        }

        return $hariKerja;
    }
}
