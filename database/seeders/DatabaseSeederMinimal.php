<?php

namespace Database\Seeders;

use App\Models\KelasReguler;
use Illuminate\Database\Seeder;

/**
 * SEEDER MINIMAL — Deploy aplikasi baru yang bersih.
 *
 * Yang di-seed:
 *   1. Kelas Reguler 1A-6B (FK dasar untuk siswa) — via KelasRegulerOnlySeeder,
 *      tanpa kelas tartil dan tanpa guru.
 *   2. Admin tunggal (admin@tartil.id) — tanpa user guru.
 *   3. Surat Al-Quran (FK untuk jurnal).
 *   4. Kop Surat Rapor default.
 *   5. Indikator Penilaian default (BQ 1, BQ 2, BQ 3, BQ 4, Tartil, Tahfidz).
 *   6. Mapping Juz-Surat (untuk perhitungan persentase hafalan).
 *
 * Yang TIDAK di-seed (admin buat sendiri via dashboard):
 *   - Tahun Ajaran & Semester
 *   - Guru & User Guru
 *   - Kelas Tartil
 *   - Siswa
 *   - Jurnal, Penilaian, Munaqosyah
 *   - Data dummy
 */
class DatabaseSeederMinimal extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== SEEDER MINIMAL TARTIL ===');
        $this->command->info('');

        // 1. Kelas Reguler (1A-6B) — tanpa kelas tartil & tanpa guru
        $this->call(KelasRegulerOnlySeeder::class);

        // 2. Admin tunggal — tanpa user guru
        $this->call(AdminOnlySeeder::class);

        // 3. Surat Al-Quran (FK untuk jurnal & munaqosyah)
        $this->call(SuratSeeder::class);

        // 4. Kop Surat Rapor default
        $this->call(KopSuratRaporSeeder::class);

        // 5. Indikator Penilaian default per jenis kelas
        $this->call(IndikatorSeeder::class);

        // 6. Mapping Juz-Surat untuk perhitungan persentase hafalan
        $this->call(JuzSuratSeeder::class);

        // Verifikasi
        $this->command->info('');
        $this->command->info('=== VERIFIKASI ===');
        $this->command->info('Kelas Reguler : '.KelasReguler::count().' record');

        $this->command->info('');
        $this->command->info('=== SEEDER SELESAI ===');
        $this->command->info('');
        $this->command->info('  Akun Admin  : admin@tartil.id / admin123');
        $this->command->info('');
        $this->command->info('  Data yang masih KOSONG (diisi manual admin):');
        $this->command->info('  - Tahun Ajaran & Semester : Buat via menu Tahun Ajaran');
        $this->command->info('  - Guru & Kelas Tartil     : Buat via menu Guru & Kelas');
        $this->command->info('  - Siswa                   : Tambah via menu Siswa');
        $this->command->info('  - Jurnal                  : Input via menu Jurnal (guru)');
        $this->command->info('  - Penilaian               : Input via menu Penilaian Rapor (guru)');
        $this->command->info('  - Munaqosyah              : Buat via menu Munaqosyah (admin)');
    }
}
