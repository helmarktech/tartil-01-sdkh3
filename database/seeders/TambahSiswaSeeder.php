<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;
use App\Models\Kelas;
use Illuminate\Support\Facades\Hash;

class TambahSiswaSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil kelas tartil yang ada
        $kelas1 = Kelas::where('jenis', 'Tahsin Pemula A')->first();
        $kelas2 = Kelas::where('jenis', 'Tahsin Pemula B')->first();
        $kelas3 = Kelas::where('jenis', 'Tartil Lanjutan')->first();

        if (!$kelas1 || !$kelas2 || !$kelas3) {
            $this->command->warn('Kelas tartil belum ada. Jalankan DatabaseSeeder dulu.');
            return;
        }

        // Hitung NIS berikutnya dari siswa terakhir
        $lastNis = Siswa::orderBy('id', 'desc')->value('nis') ?? '2025000';
        $nextNum = (int) substr($lastNis, 4) + 1;

        // Data siswa tambahan — distribusi ke 3 kelas
        $siswaTambahan = [
            // Kelas 1 (5 siswa tambahan = total 8)
            ['nama' => 'Ahmad Rizky', 'jk' => 'L', 'reguler' => 2, 'tl' => 'Surabaya', 'tgl' => '2012-01-10', 'ayah' => 'H. Candra Wijaya', 'kelas_tartil' => $kelas1->id],
            ['nama' => 'Nurul Hidayah', 'jk' => 'P', 'reguler' => 2, 'tl' => 'Yogyakarta', 'tgl' => '2011-05-20', 'ayah' => 'H. Dedi Kurniawan', 'kelas_tartil' => $kelas1->id],
            ['nama' => 'Abdullah Karim', 'jk' => 'L', 'reguler' => 1, 'tl' => 'Semarang', 'tgl' => '2012-09-05', 'ayah' => 'H. Eko Prasetyo', 'kelas_tartil' => $kelas1->id],
            ['nama' => 'Siti Maryam', 'jk' => 'P', 'reguler' => 1, 'tl' => 'Malang', 'tgl' => '2011-11-12', 'ayah' => 'H. Faisal Rahman', 'kelas_tartil' => $kelas1->id],
            ['nama' => 'Yusuf Hidayat', 'jk' => 'L', 'reguler' => 1, 'tl' => 'Makassar', 'tgl' => '2012-04-18', 'ayah' => 'H. Gunawan Setyadi', 'kelas_tartil' => $kelas1->id],
            // Kelas 2 (6 siswa tambahan = total 8)
            ['nama' => 'Bilal Anwar', 'jk' => 'L', 'reguler' => 3, 'tl' => 'Medan', 'tgl' => '2010-06-25', 'ayah' => 'H. Iwan Setiawan', 'kelas_tartil' => $kelas2->id],
            ['nama' => 'Zahrotus Shita', 'jk' => 'P', 'reguler' => 3, 'tl' => 'Denpasar', 'tgl' => '2010-02-14', 'ayah' => 'H. Joko Widodo', 'kelas_tartil' => $kelas2->id],
            ['nama' => 'Hafizh Ramadhan', 'jk' => 'L', 'reguler' => 2, 'tl' => 'Banjarmasin', 'tgl' => '2010-09-08', 'ayah' => 'H. Kurniawan Agung', 'kelas_tartil' => $kelas2->id],
            ['nama' => 'Salma Fauziah', 'jk' => 'P', 'reguler' => 2, 'tl' => 'Pontianak', 'tgl' => '2010-12-01', 'ayah' => 'H. Lukman Hakim', 'kelas_tartil' => $kelas2->id],
            ['nama' => 'Daffa Al-Kahfi', 'jk' => 'L', 'reguler' => 3, 'tl' => 'Manado', 'tgl' => '2010-07-19', 'ayah' => 'H. Maman Abdurrahman', 'kelas_tartil' => $kelas2->id],
            ['nama' => 'Alya Nabila', 'jk' => 'P', 'reguler' => 3, 'tl' => 'Padang', 'tgl' => '2010-04-22', 'ayah' => 'H. Nana Suryana', 'kelas_tartil' => $kelas2->id],
            // Kelas 3 (6 siswa tambahan = total 8)
            ['nama' => 'Rafif Athallah', 'jk' => 'L', 'reguler' => 4, 'tl' => 'Bandung', 'tgl' => '2009-12-07', 'ayah' => 'H. Oki Setiawan', 'kelas_tartil' => $kelas3->id],
            ['nama' => 'Sabrina Azzahra', 'jk' => 'P', 'reguler' => 4, 'tl' => 'Jakarta', 'tgl' => '2009-08-15', 'ayah' => 'H. Putra Wijaya', 'kelas_tartil' => $kelas3->id],
            ['nama' => 'Zaidan Fikri', 'jk' => 'L', 'reguler' => 4, 'tl' => 'Surabaya', 'tgl' => '2009-10-28', 'ayah' => 'H. Qomaruddin', 'kelas_tartil' => $kelas3->id],
            ['nama' => 'Citra Lestari', 'jk' => 'P', 'reguler' => 4, 'tl' => 'Yogyakarta', 'tgl' => '2009-06-11', 'ayah' => 'H. Rudianto', 'kelas_tartil' => $kelas3->id],
            ['nama' => 'Aminah Khairunnisa', 'jk' => 'P', 'reguler' => 5, 'tl' => 'Palembang', 'tgl' => '2011-08-30', 'ayah' => 'H. Taufik Hidayat', 'kelas_tartil' => $kelas3->id],
            ['nama' => 'Agus Astini', 'jk' => 'L', 'reguler' => 6, 'tl' => 'Lamongan', 'tgl' => '1987-03-25', 'ayah' => 'Saiman', 'kelas_tartil' => $kelas3->id],
        ];

        $created = 0;
        foreach ($siswaTambahan as $s) {
            // Cek duplikat nama
            if (Siswa::where('nama', $s['nama'])->exists()) {
                $this->command->info("Skip: {$s['nama']} sudah ada");
                continue;
            }

            $nis = '2025' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
            Siswa::create([
                'nis' => $nis,
                'nama' => $s['nama'],
                'no_hp' => '08' . rand(1000000000, 9999999999),
                'password' => Hash::make($nis),
                'jenis_kelamin' => $s['jk'],
                'kelas_reguler_id' => $s['reguler'],
                'kelas_tartil_id' => $s['kelas_tartil'],
                'tanggal_masuk' => '2025-07-01',
                'tempat_lahir' => $s['tl'],
                'tanggal_lahir' => $s['tgl'],
                'nama_ayah' => $s['ayah'],
            ]);
            $nextNum++;
            $created++;
        }

        $this->command->info("{$created} siswa tambahan berhasil dibuat.");
    }
}
