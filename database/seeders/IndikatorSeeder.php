<?php

namespace Database\Seeders;

use App\Models\IndikatorPenilaian;
use Illuminate\Database\Seeder;

class IndikatorSeeder extends Seeder
{
    /**
     * Data indikator default per jenis kelas tartil.
     * Format jenis harus sesuai enum di tabel kelas:
     * 'BQ 1', 'BQ 2', 'BQ 3', 'BQ 4', 'Tartil', 'Tahfidz'
     */
    private array $data = [
        'BQ 1' => [
            'MURO\'ATUL HURUF & HAROKAT',
            'MAKHARIJUL HURUF',
            'IRAMA ROST PIQ',
            'KELANCARAN',
        ],
        'BQ 2' => [
            'MURO\'ATUL HURUF & HAROKAT',
            'MAKHARIJUL HURUF',
            'SHIFATUL HURUF',
            'HURUF SAMBUNG',
            'TANWIN SUKUN',
            'MAD TOBI\'I',
            'IDHAR QOMARI',
            'IRAMA ROST PIQ',
            'KELANCARAN',
        ],
        'BQ 3' => [
            'MURO\'ATUL HURUF & HAROKAT',
            'MAKHARIJUL HURUF',
            'SHIFATUL HURUF',
            'AHKAMUL MIM, NUN & TANWIN',
            'AHKAMUL MAD',
            'LAM JALALAH',
            'TASYDID',
            'QOLQOLAH',
            'GHUNNAH',
            'IRAMA ROST PIQ',
            'KELANCARAN',
        ],
        'BQ 4' => [
            'MURO\'ATUL HURUF & HAROKAT',
            'MAKHARIJUL HURUF',
            'SHIFATUL HURUF',
            'GHOROIB',
            'AHKAMUL HURUF',
            'AHKAMUL MAD',
            'WAQOF WAL IBTIDA\'',
            'IRAMA ROST PIQ',
            'KELANCARAN',
        ],
        // Tartil = sama persis dengan BQ 4
        'Tartil' => [
            'MURO\'ATUL HURUF & HAROKAT',
            'MAKHARIJUL HURUF',
            'SHIFATUL HURUF',
            'GHOROIB',
            'AHKAMUL HURUF',
            'AHKAMUL MAD',
            'WAQOF WAL IBTIDA\'',
            'IRAMA ROST PIQ',
            'KELANCARAN',
        ],
        // Tahfidz = indikator khusus hafalan
        'Tahfidz' => [
            'KELANCARAN',
            'TAJWID',
            'FASHOHAH',
        ],
    ];

    public function run(): void
    {
        foreach ($this->data as $jenis => $indikators) {
            foreach ($indikators as $index => $nama) {
                IndikatorPenilaian::firstOrCreate(
                    [
                        'jenis_kelas' => $jenis,
                        'nama_indikator' => $nama,
                    ],
                    [
                        'urutan' => $index + 1,
                        'is_default' => true,
                    ]
                );
            }
        }

        $this->command->info('Indikator penilaian BQ 1, BQ 2, BQ 3, BQ 4, Tartil, Tahfidz berhasil di-seed.');
    }
}
