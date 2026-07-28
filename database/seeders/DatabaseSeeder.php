<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TahunAjaran;
use App\Models\Semester;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== SEEDER KOMPREHENSIF TARTIL ===');
        $this->command->info('');

        // 1. Tahun Ajaran + Semester (Ganjil=ditutup, Genap=aktif)
        $this->call(TahunAjaranSemesterSeeder::class);

        // 2. Kelas Reguler, Kelas Tartil, Guru (6 guru untuk 6 kelas)
        $this->call(KelasGuruSeeder::class);

        // 3. Admin + User Guru (HARUS setelah KelasGuruSeeder agar guru punya kelas)
        $this->call(AdminSeeder::class);

        // 4. Surat Al-Quran
        $this->call(SuratSeeder::class);

        // 5. 30 Siswa
        $this->call(Siswa30Seeder::class);

        // 6. Jurnal, Penilaian, Munaqosyah, Rekap R2, Snapshot Semester
        $this->call(JurnalPenilaianMunaqosyahSeeder::class);

        // ════════════════════════════════════════════
        // VERIFIKASI: Pastikan TA dan Semester terbuat
        // ════════════════════════════════════════════
        $this->command->info('');
        $this->command->info('=== VERIFIKASI DATA ===');

        $taCount = TahunAjaran::count();
        $semCount = Semester::count();
        $this->command->info("TahunAjaran: {$taCount} record");
        $this->command->info("Semester: {$semCount} record");

        foreach (Semester::orderBy('tanggal_mulai')->get() as $s) {
            $this->command->info("  - {$s->nama} | {$s->tahun_ajaran} | status={$s->status} | aktif=" . ($s->is_aktif ? 'yes' : 'no'));
        }

        if ($taCount === 0) {
            $this->command->error('WARNING: TahunAjaran kosong! Audit Semester tidak akan berfungsi.');
        }
        if ($semCount === 0) {
            $this->command->error('WARNING: Semester kosong! Sistem tidak bisa digunakan.');
        }

        $this->command->info('');
        $this->command->info('=== SEEDER SELESAI ===');
        $this->command->info('Login Admin  : admin@tartil.id / admin123');
        // Info login guru akan ditampilkan oleh AdminSeeder
        $this->command->info('Login Siswa  : NIS 2526001 s/d 2526030 / password: password');
    }
}
