<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminOnlySeeder extends Seeder
{
    /**
     * Seed satu akun admin saja, tanpa user guru.
     * Guru dan akunnya akan dibuat oleh admin setelah deploy awal.
     */
    public function run(): void
    {
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

        $this->command->info('Akun Admin berhasil di-seed: admin@tartil.id / admin123');
    }
}
