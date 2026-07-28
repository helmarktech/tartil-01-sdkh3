<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndikatorPenilaian extends Model
{
    use HasFactory;

    protected $table = 'indikator_penilaians';

    protected $fillable = [
        'jenis_kelas',
        'nama_indikator',
        'urutan',
        'is_default',
    ];

    /**
     * Ambil semua indikator untuk jenis kelas tertentu, terurut.
     */
    public static function byJenis(string $jenis)
    {
        return static::where('jenis_kelas', $jenis)
            ->orderBy('urutan')
            ->get();
    }

    /**
     * Daftar jenis kelas tartil yang tersedia (sesuai enum di tabel kelas).
     */
    public static function jenisKelasList(): array
    {
        return ['BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz'];
    }
}
