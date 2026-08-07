<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPendampinganOrtu extends Model
{
    protected $table = 'laporan_pendampingan_ortus';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'semester_id',
        'guru_id',
        'jenis',
        'surat_id',
        'ayat_mulai',
        'ayat_selesai',
        'tanggal',
        'catatan',
        'status',
        'dikonfirmasi_oleh',
        'tanggal_konfirmasi',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'tanggal_konfirmasi' => 'datetime',
        'ayat_mulai' => 'integer',
        'ayat_selesai' => 'integer',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function guruKonfirmasi()
    {
        return $this->belongsTo(Guru::class, 'dikonfirmasi_oleh');
    }

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function scopePengajuan($query)
    {
        return $query->where('status', 'pengajuan_konfirmasi');
    }

    public function scopeDikonfirmasi($query)
    {
        return $query->where('status', 'telah_dikonfirmasi');
    }

    public function scopeUntukGuru($query, int $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    public function scopeUntukSiswa($query, int $siswaId)
    {
        return $query->where('siswa_id', $siswaId);
    }

    public static function labelStatus(string $status): string
    {
        return match ($status) {
            'pengajuan_konfirmasi' => 'Pengajuan Konfirmasi',
            'telah_dikonfirmasi' => 'Telah Dikonfirmasi',
            default => $status,
        };
    }

    public static function labelJenis(string $jenis): string
    {
        return match ($jenis) {
            'tadarus' => 'Tadarus',
            'murajaah' => 'Murajaah',
            default => $jenis,
        };
    }

    public function isDikonfirmasi(): bool
    {
        return $this->status === 'telah_dikonfirmasi' && $this->dikonfirmasi_oleh !== null;
    }
}
