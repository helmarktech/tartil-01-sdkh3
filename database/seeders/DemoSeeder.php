<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\KelasReguler;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Tahun Ajaran ──
        $ta = TahunAjaran::firstOrCreate(
            ['nama' => '2025/2026'],
            [
                'tanggal_mulai' => Carbon::parse('2025-07-01'),
                'tanggal_selesai' => Carbon::parse('2026-06-30'),
                'status' => 'aktif',
            ]
        );

        // ── 2. Semester: gunakan semester Genap 2025/2026 yang sudah dibuat DatabaseSeeder ──
        // Pastikan tanggalnya mencakup HARI INI agar jurnal bisa diisi
        $today = Carbon::now();
        $semester = Semester::where('tahun_ajaran', '2025/2026')
            ->where('jenis', 'genap')
            ->first();

        if (!$semester) {
            $semester = Semester::create([
                'tahun_ajaran' => '2025/2026',
                'jenis' => 'genap',
                'tanggal_mulai' => $today->copy()->startOfYear(),
                'tanggal_selesai' => $today->copy()->endOfYear(),
                'is_aktif' => true,
            ]);
        }

        // Pastikan semester ini aktif dan mencakup hari ini
        Semester::where('is_aktif', true)->where('id', '!=', $semester->id)->update(['is_aktif' => false]);
        $semester->update([
            'is_aktif' => true,
            'tanggal_mulai' => $today->copy()->startOfYear(),
            'tanggal_selesai' => $today->copy()->endOfYear(),
        ]);

        // ── 3. Kelas Reguler (perlu untuk siswa) ──
        $kelasReguler = KelasReguler::firstOrCreate(
            ['nama' => '1A'],
            [
                'jenjang' => 1,
                'tingkat' => 'A',
                'is_aktif' => true,
            ]
        );

        // ── 4. Kelas Tartil per Jenis ──
        // Format harus sesuai enum di tabel kelas: 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz'
        $jenisList = ['BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz'];
        $guruId = \App\Models\Guru::where('email', 'ahmad.ridwan@tartil.id')->value('id');

        foreach ($jenisList as $i => $jenis) {
            $kelas = Kelas::firstOrCreate(
                ['nama' => $jenis . ' - Kelas ' . ($i + 1)],
                [
                    'jenis' => $jenis,
                    'mata_pelajaran' => 'Tartil',
                    'deskripsi' => 'Kelas ' . $jenis,
                    'guru_id' => $guruId,
                    'status' => 'aktif',
                ]
            );

            // ── 5. Siswa per kelas ──
            for ($j = 1; $j <= 3; $j++) {
                $nis = str_replace(' ', '', $jenis) . str_pad($j, 3, '0', STR_PAD_LEFT);
                Siswa::firstOrCreate(
                    ['nis' => $nis],
                    [
                        'nama' => $jenis . ' - Siswa ' . $j,
                        'password' => bcrypt('siswa123'),
                        'no_hp' => '08' . rand(1000000000, 9999999999),
                        'jenis_kelamin' => $j % 2 === 1 ? 'L' : 'P',
                        'kelas_reguler_id' => $kelasReguler->id,
                        'kelas_tartil_id' => $kelas->id,
                        'status' => 'aktif',
                        'tanggal_masuk' => Carbon::parse('2025-07-01'),
                    ]
                );
            }
        }

        $this->command->info('Data demo berhasil dibuat:');
        $this->command->info('  - 1 Tahun Ajaran (2025/2026)');
        $this->command->info('  - 1 Semester aktif (Genap 2025/2026)');
        $this->command->info('  - 6 Kelas Tartil (BQ1-BQ4, TARTIL, TAHFIDZ)');
        $this->command->info('  - 18 Siswa (3 per kelas)');
    }
}
