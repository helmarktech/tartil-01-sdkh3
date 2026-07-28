<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KelasReguler;
use App\Models\Kelas;
use App\Models\Guru;

class KelasGuruSeeder extends Seeder
{
    public function run(): void
    {
        // ═══ Kelas Reguler 1-6 ═══
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

        // ═══ Kelas Tartil — 6 kelas ═══
        // Mata pelajaran = jenis kelas (BQ 1, BQ 2, BQ 3, BQ 4, Tartil, Tahfidz)
        $tartilData = [
            ['nama' => 'BQ 1', 'jenis' => 'BQ 1', 'mapel' => 'BQ 1'],
            ['nama' => 'BQ 2', 'jenis' => 'BQ 2', 'mapel' => 'BQ 2'],
            ['nama' => 'BQ 3', 'jenis' => 'BQ 3', 'mapel' => 'BQ 3'],
            ['nama' => 'BQ 4', 'jenis' => 'BQ 4', 'mapel' => 'BQ 4'],
            ['nama' => 'Tartil', 'jenis' => 'Tartil', 'mapel' => 'Tartil'],
            ['nama' => 'Tahfidz', 'jenis' => 'Tahfidz', 'mapel' => 'Tahfidz'],
        ];

        $guruNames = ['Ust. Ahmad', 'Ust. Budi', 'Ust. Citra', 'Ust. Dedi', 'Ust. Eka', 'Ust. Fajar'];
        $guruEmails = ['ahmad@tartil.id', 'budi@tartil.id', 'citra@tartil.id', 'dedi@tartil.id', 'eka@tartil.id', 'fajar@tartil.id'];
        $guruHp = ['081200000001', '081200000002', '081200000003', '081200000004', '081200000005', '081200000006'];

        foreach ($tartilData as $i => $td) {
            // Buat guru jika belum ada
            $guru = Guru::firstOrCreate(
                ['email' => $guruEmails[$i]],
                [
                    'nama' => $guruNames[$i],
                    'nip' => 'GT' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                    'no_hp' => $guruHp[$i],
                    'is_aktif' => true,
                ]
            );

            Kelas::firstOrCreate(
                ['nama' => $td['nama']],
                [
                    'jenis' => $td['jenis'],
                    'mata_pelajaran' => $td['mapel'],
                    'guru_id' => $guru->id,
                    'status' => 'aktif',
                ]
            );
        }

        $this->command->info("Kelas Reguler: 12, Kelas Tartil: 6, Guru: 6 — created.");
    }
}
