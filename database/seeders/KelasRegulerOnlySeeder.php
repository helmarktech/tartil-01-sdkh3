<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KelasReguler;

class KelasRegulerOnlySeeder extends Seeder
{
    /**
     * Seed hanya kelas reguler (1A-6B) tanpa guru dan tanpa kelas tartil.
     * Kelas tartil akan dibuat oleh admin setelah deploy awal.
     */
    public function run(): void
    {
        $regulerData = [
            ['nama' => '1A', 'jenjang' => 1, 'tingkat' => 'A'],
            ['nama' => '1B', 'jenjang' => 1, 'tingkat' => 'B'],
            ['nama' => '2A', 'jenjang' => 2, 'tingkat' => 'A'],
            ['nama' => '2B', 'jenjang' => 2, 'tingkat' => 'B'],
            ['nama' => '3A', 'jenjang' => 3, 'tingkat' => 'A'],
            ['nama' => '3B', 'jenjang' => 3, 'tingkat' => 'B'],
            ['nama' => '4A', 'jenjang' => 4, 'tingkat' => 'A'],
            ['nama' => '4B', 'jenjang' => 4, 'tingkat' => 'B'],
            ['nama' => '5A', 'jenjang' => 5, 'tingkat' => 'A'],
            ['nama' => '5B', 'jenjang' => 5, 'tingkat' => 'B'],
            ['nama' => '6A', 'jenjang' => 6, 'tingkat' => 'A'],
            ['nama' => '6B', 'jenjang' => 6, 'tingkat' => 'B'],
        ];

        foreach ($regulerData as $rd) {
            KelasReguler::firstOrCreate(
                ['nama' => $rd['nama']],
                ['jenjang' => $rd['jenjang'], 'tingkat' => $rd['tingkat']]
            );
        }

        $this->command->info('Kelas Reguler 1A-6B berhasil di-seed (tanpa kelas tartil & guru).');
    }
}
