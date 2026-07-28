<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KopSuratRapor;

class KopSuratRaporSeeder extends Seeder
{
    public function run(): void
    {
        KopSuratRapor::firstOrCreate([], [
            'judul' => 'LAPORAN HASIL BELAJAR',
            'sub_judul' => 'Program Pembelajaran Al-Quran (Tartil)',
            'nama_sekolah' => 'Nama Sekolah Anda',
            'alamat' => 'Alamat Sekolah',
            'telepon' => '-',
            'email' => '-',
            'website' => '-',
            'tahun_ajaran' => date('Y') . '/' . (date('Y') + 1),
            'catatan_kaki' => 'Rapor ini adalah dokumen resmi hasil pembelajaran tartil.',
            'kepala_sekolah' => '........................',
            'nip_kepala_sekolah' => '-',
        ]);
    }
}
