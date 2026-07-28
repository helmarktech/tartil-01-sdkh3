<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianRapor extends Model
{
    use HasFactory;

    protected $table = 'penilaian_rapors';

    protected $fillable = [
        'semester_penilaian_rapor_id',
        'siswa_id',
        'indikator_penilaian_id',
        'nilai_angka',
        'nilai_huruf',
        'catatan',
        'diisi_oleh',
        'tanggal_diisi',
    ];

    protected $casts = [
        'tanggal_diisi' => 'datetime',
    ];

    // ── Relations ──

    public function semesterPenilaian()
    {
        return $this->belongsTo(SemesterPenilaianRapor::class, 'semester_penilaian_rapor_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function indikator()
    {
        return $this->belongsTo(IndikatorPenilaian::class, 'indikator_penilaian_id');
    }

    public function pengisi()
    {
        return $this->belongsTo(Guru::class, 'diisi_oleh');
    }

    // ── Helpers ──

    /**
     * Konversi nilai angka (0-100) ke huruf.
     */
    public static function angkaKeHuruf(?int $nilai): ?string
    {
        if ($nilai === null) return null;
        return match (true) {
            $nilai >= 85 => 'A',
            $nilai >= 70 => 'B',
            $nilai >= 60 => 'C',
            default => 'K',
        };
    }

    /**
     * Set nilai angka dan otomatis update nilai huruf.
     */
    public function setNilai(int $angka, ?int $guruId = null): void
    {
        $this->nilai_angka = $angka;
        $this->nilai_huruf = static::angkaKeHuruf($angka);
        if ($guruId) {
            $this->diisi_oleh = $guruId;
        }
        $this->tanggal_diisi = now();
        $this->save();
    }

    /**
     * Cek apakah sudah diisi.
     */
    public function isDiisi(): bool
    {
        return $this->nilai_angka !== null;
    }

    /**
     * Warna badge berdasarkan nilai huruf.
     */
    public function badgeClass(): string
    {
        return match ($this->nilai_huruf) {
            'A' => 'badge-success',
            'B' => 'badge-primary',
            'C' => 'badge-warning',
            'K' => 'badge-error',
            default => '',
        };
    }
}
