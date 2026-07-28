<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;
use App\Models\KelasReguler;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\SemesterSiswa;
use Carbon\Carbon;

class Siswa30Seeder extends Seeder
{
    public function run(): void
    {
        $kelasTartil = Kelas::where('status', 'aktif')->orderBy('id')->get();
        $kelasRegulerList = KelasReguler::all();
        $semGanjil = Semester::where('tahun_ajaran', '2025/2026')->where('jenis', 'ganjil')->first();
        $semGenap = Semester::where('tahun_ajaran', '2025/2026')->where('jenis', 'genap')->first();

        // 30 siswa — 5 per kelas tartil, terbagi ke kelas reguler 1-6
        $siswaData = [
            // Kelas Reguler 1 (5 siswa → BQ 1)
            ['nama' => 'Ahmad Rizky', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '1A')->id, 'kelas_tartil_jenis' => 'BQ 1', 'nis' => '2526001', 'jk' => 'L', 'ortu' => 'Bapak Rizky', 'hp' => '081111111001'],
            ['nama' => 'Bella Safira', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '1B')->id, 'kelas_tartil_jenis' => 'BQ 1', 'nis' => '2526002', 'jk' => 'P', 'ortu' => 'Bapak Safir', 'hp' => '081111111002'],
            ['nama' => 'Candra Wijaya', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '1A')->id, 'kelas_tartil_jenis' => 'BQ 1', 'nis' => '2526003', 'jk' => 'L', 'ortu' => 'Bapak Wijaya', 'hp' => '081111111003'],
            ['nama' => 'Dewi Lestari', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '1B')->id, 'kelas_tartil_jenis' => 'BQ 1', 'nis' => '2526004', 'jk' => 'P', 'ortu' => 'Bapak Lestari', 'hp' => '081111111004'],
            ['nama' => 'Eko Prasetyo', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '1A')->id, 'kelas_tartil_jenis' => 'BQ 1', 'nis' => '2526005', 'jk' => 'L', 'ortu' => 'Bapak Prasetyo', 'hp' => '081111111005'],

            // Kelas Reguler 2 (5 siswa → BQ 2)
            ['nama' => 'Fani Indah', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '2A')->id, 'kelas_tartil_jenis' => 'BQ 2', 'nis' => '2526006', 'jk' => 'P', 'ortu' => 'Bapak Indah', 'hp' => '081111111006'],
            ['nama' => 'Gilang Ramadhan', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '2B')->id, 'kelas_tartil_jenis' => 'BQ 2', 'nis' => '2526007', 'jk' => 'L', 'ortu' => 'Bapak Ramadhan', 'hp' => '081111111007'],
            ['nama' => 'Hana Mulyani', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '2A')->id, 'kelas_tartil_jenis' => 'BQ 2', 'nis' => '2526008', 'jk' => 'P', 'ortu' => 'Bapak Mulyani', 'hp' => '081111111008'],
            ['nama' => 'Irfan Hakim', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '2B')->id, 'kelas_tartil_jenis' => 'BQ 2', 'nis' => '2526009', 'jk' => 'L', 'ortu' => 'Bapak Hakim', 'hp' => '081111111009'],
            ['nama' => 'Jasmine Aulia', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '2A')->id, 'kelas_tartil_jenis' => 'BQ 2', 'nis' => '2526010', 'jk' => 'P', 'ortu' => 'Bapak Aulia', 'hp' => '081111111010'],

            // Kelas Reguler 3 (5 siswa → BQ 3)
            ['nama' => 'Kevin Arifin', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '3A')->id, 'kelas_tartil_jenis' => 'BQ 3', 'nis' => '2526011', 'jk' => 'L', 'ortu' => 'Bapak Arifin', 'hp' => '081111111011'],
            ['nama' => 'Lina Kusuma', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '3B')->id, 'kelas_tartil_jenis' => 'BQ 3', 'nis' => '2526012', 'jk' => 'P', 'ortu' => 'Bapak Kusuma', 'hp' => '081111111012'],
            ['nama' => 'Mario Santoso', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '3A')->id, 'kelas_tartil_jenis' => 'BQ 3', 'nis' => '2526013', 'jk' => 'L', 'ortu' => 'Bapak Santoso', 'hp' => '081111111013'],
            ['nama' => 'Nadia Fitri', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '3B')->id, 'kelas_tartil_jenis' => 'BQ 3', 'nis' => '2526014', 'jk' => 'P', 'ortu' => 'Bapak Fitri', 'hp' => '081111111014'],
            ['nama' => 'Oscar Putra', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '3A')->id, 'kelas_tartil_jenis' => 'BQ 3', 'nis' => '2526015', 'jk' => 'L', 'ortu' => 'Bapak Putra', 'hp' => '081111111015'],

            // Kelas Reguler 4 (5 siswa → BQ 4)
            ['nama' => 'Putri Anggraini', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '4A')->id, 'kelas_tartil_jenis' => 'BQ 4', 'nis' => '2526016', 'jk' => 'P', 'ortu' => 'Bapak Anggraini', 'hp' => '081111111016'],
            ['nama' => 'Raka Mahendra', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '4B')->id, 'kelas_tartil_jenis' => 'BQ 4', 'nis' => '2526017', 'jk' => 'L', 'ortu' => 'Bapak Mahendra', 'hp' => '081111111017'],
            ['nama' => 'Sinta Dewi', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '4A')->id, 'kelas_tartil_jenis' => 'BQ 4', 'nis' => '2526018', 'jk' => 'P', 'ortu' => 'Bapak Dewi', 'hp' => '081111111018'],
            ['nama' => 'Taufik Hidayat', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '4B')->id, 'kelas_tartil_jenis' => 'BQ 4', 'nis' => '2526019', 'jk' => 'L', 'ortu' => 'Bapak Hidayat', 'hp' => '081111111019'],
            ['nama' => 'Ulya Rahmawati', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '4A')->id, 'kelas_tartil_jenis' => 'BQ 4', 'nis' => '2526020', 'jk' => 'P', 'ortu' => 'Bapak Rahmawati', 'hp' => '081111111020'],

            // Kelas Reguler 5 (5 siswa → Tartil)
            ['nama' => 'Vino Bastian', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '5A')->id, 'kelas_tartil_jenis' => 'Tartil', 'nis' => '2526021', 'jk' => 'L', 'ortu' => 'Bapak Bastian', 'hp' => '081111111021'],
            ['nama' => 'Winda Sari', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '5B')->id, 'kelas_tartil_jenis' => 'Tartil', 'nis' => '2526022', 'jk' => 'P', 'ortu' => 'Bapak Sari', 'hp' => '081111111022'],
            ['nama' => 'Xaverius Tan', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '5A')->id, 'kelas_tartil_jenis' => 'Tartil', 'nis' => '2526023', 'jk' => 'L', 'ortu' => 'Bapak Tan', 'hp' => '081111111023'],
            ['nama' => 'Yuni Septiani', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '5B')->id, 'kelas_tartil_jenis' => 'Tartil', 'nis' => '2526024', 'jk' => 'P', 'ortu' => 'Bapak Septiani', 'hp' => '081111111024'],
            ['nama' => 'Zaki Firmansyah', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '5A')->id, 'kelas_tartil_jenis' => 'Tartil', 'nis' => '2526025', 'jk' => 'L', 'ortu' => 'Bapak Firmansyah', 'hp' => '081111111025'],

            // Kelas Reguler 6 (5 siswa → Tahfidz)
            ['nama' => 'Adinda Kirana', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '6A')->id, 'kelas_tartil_jenis' => 'Tahfidz', 'nis' => '2526026', 'jk' => 'P', 'ortu' => 'Bapak Kirana', 'hp' => '081111111026'],
            ['nama' => 'Bagas Nugroho', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '6B')->id, 'kelas_tartil_jenis' => 'Tahfidz', 'nis' => '2526027', 'jk' => 'L', 'ortu' => 'Bapak Nugroho', 'hp' => '081111111027'],
            ['nama' => 'Cecilia Novita', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '6A')->id, 'kelas_tartil_jenis' => 'Tahfidz', 'nis' => '2526028', 'jk' => 'P', 'ortu' => 'Bapak Novita', 'hp' => '081111111028'],
            ['nama' => 'Daffa Pratama', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '6B')->id, 'kelas_tartil_jenis' => 'Tahfidz', 'nis' => '2526029', 'jk' => 'L', 'ortu' => 'Bapak Pratama', 'hp' => '081111111029'],
            ['nama' => 'Erlin Suryani', 'kelas_reguler_id' => $kelasRegulerList->firstWhere('nama', '6A')->id, 'kelas_tartil_jenis' => 'Tahfidz', 'nis' => '2526030', 'jk' => 'P', 'ortu' => 'Bapak Suryani', 'hp' => '081111111030'],
        ];

        $count = 0;
        foreach ($siswaData as $i => $sd) {
            $kelasItem = $kelasTartil->firstWhere('jenis', $sd['kelas_tartil_jenis']);
            if (!$kelasItem) {
                $this->command->warn("Kelas tartil '{$sd['kelas_tartil_jenis']}' tidak ditemukan, skip siswa {$sd['nama']}");
                continue;
            }
            $kelasTartilId = $kelasItem->id;

            $siswa = Siswa::firstOrCreate(
                ['nis' => $sd['nis']],
                [
                    'nama' => $sd['nama'],
                    'jenis_kelamin' => $sd['jk'],
                    'no_hp' => $sd['nis'], // login pakai NIS
                    'tanggal_lahir' => Carbon::now()->subYears(7 + (int)substr($sd['kelas_tartil_jenis'], -1))->subMonths(rand(1, 11)),
                    'nama_ayah' => $sd['ortu'],
                    'no_hp_ortu' => $sd['hp'],
                    'kelas_reguler_id' => $sd['kelas_reguler_id'],
                    'kelas_tartil_id' => $kelasTartilId,
                    'tanggal_masuk' => '2025-07-01',
                    'password' => bcrypt('password'),
                    'status' => 'aktif',
                ]
            );

            // Penempatan kelas untuk semester Ganjil
            if ($semGanjil) {
                SemesterSiswa::firstOrCreate(
                    ['semester_id' => $semGanjil->id, 'siswa_id' => $siswa->id],
                    ['kelas_id' => $kelasTartilId, 'kelas_reguler_id' => $sd['kelas_reguler_id']]
                );
            }

            // Penempatan kelas untuk semester Genap
            if ($semGenap) {
                SemesterSiswa::firstOrCreate(
                    ['semester_id' => $semGenap->id, 'siswa_id' => $siswa->id],
                    ['kelas_id' => $kelasTartilId, 'kelas_reguler_id' => $sd['kelas_reguler_id']]
                );
            }

            $count++;
        }

        $this->command->info("Siswa: {$count} — created (5 per kelas tartil, kelas reguler 1-6).");
    }
}
