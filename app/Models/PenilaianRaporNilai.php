<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenilaianRaporNilai extends Model
{
    use \App\Traits\Auditable;
    protected $table = 'penilaian_rapor_nilais';

    protected $fillable = [
        'penilaian_id', 'siswa_id', 'indikator_penilaian_id',
        'nilai', 'catatan', 'diisi_oleh', 'tanggal_diisi'
    ];

    // ── Relations ──

    public function penilaian()
    {
        return $this->belongsTo(PenilaianRaporInternal::class, 'penilaian_id');
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
}
