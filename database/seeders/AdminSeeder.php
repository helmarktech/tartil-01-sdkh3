<?php

namespace Database\Seeders;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Buat akun: 1 admin + user guru untuk guru yang sudah ada.
     * Guru HARUS sudah dibuat oleh KelasGuruSeeder terlebih dahulu.
     */
    public function run(): void
    {
        // ════════════════════════════════════════════
        // 1 ADMIN
        // ════════════════════════════════════════════
        User::firstOrCreate(
            ['email' => 'admin@tartil.id'],
            [
                'nama' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'guru_id' => null,
                'is_aktif' => true,
            ]
        );

        // ════════════════════════════════════════════
        // 2 USER GURU — untuk guru yang sudah ada di DB
        // Ambil 2 guru pertama yang punya kelas (dari KelasGuruSeeder)
        // ════════════════════════════════════════════
        $gurus = Guru::where('is_aktif', true)
            ->whereHas('kelas') // hanya guru yang punya kelas
            ->orderBy('id')
            ->take(2)
            ->get();

        if ($gurus->count() === 0) {
            // Fallback: ambil 2 guru pertama tanpa filter kelas
            $gurus = Guru::where('is_aktif', true)
                ->orderBy('id')
                ->take(2)
                ->get();
        }

        foreach ($gurus as $guru) {
            User::firstOrCreate(
                ['email' => $guru->email],
                [
                    'nama' => $guru->nama,
                    'password' => Hash::make('guru123'),
                    'role' => 'guru',
                    'guru_id' => $guru->id,
                    'is_aktif' => true,
                ]
            );
        }

        $this->command->info('Admin + Guru user berhasil dibuat:');
        $this->command->info('  Admin  : admin@tartil.id / admin123');
        foreach ($gurus as $i => $g) {
            $this->command->info('  Guru ' . ($i + 1) . ': ' . $g->email . ' / guru123');
        }
    }
}
