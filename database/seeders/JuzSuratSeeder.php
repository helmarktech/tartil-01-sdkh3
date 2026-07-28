<?php

namespace Database\Seeders;

use App\Models\Surat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JuzSuratSeeder extends Seeder
{
    /**
     * Mapping standar juz 1-30 ke surat dan rentang ayat.
     * Data diambil dari pembagian juz Al-Quran standar (Mushaf Madinah).
     */
    private array $mapping = [
        // Juz 1
        ['juz' => 1, 'surat_urutan' => 1, 'ayat_mulai' => 1, 'ayat_selesai' => 7],      // Al-Fatihah
        ['juz' => 1, 'surat_urutan' => 2, 'ayat_mulai' => 1, 'ayat_selesai' => 141],    // Al-Baqarah

        // Juz 2
        ['juz' => 2, 'surat_urutan' => 2, 'ayat_mulai' => 142, 'ayat_selesai' => 252], // Al-Baqarah

        // Juz 3
        ['juz' => 3, 'surat_urutan' => 2, 'ayat_mulai' => 253, 'ayat_selesai' => 286], // Al-Baqarah
        ['juz' => 3, 'surat_urutan' => 3, 'ayat_mulai' => 1, 'ayat_selesai' => 92],    // Ali Imran

        // Juz 4
        ['juz' => 4, 'surat_urutan' => 3, 'ayat_mulai' => 93, 'ayat_selesai' => 200],  // Ali Imran
        ['juz' => 4, 'surat_urutan' => 4, 'ayat_mulai' => 1, 'ayat_selesai' => 23],     // An-Nisa

        // Juz 5
        ['juz' => 5, 'surat_urutan' => 4, 'ayat_mulai' => 24, 'ayat_selesai' => 147],   // An-Nisa

        // Juz 6
        ['juz' => 6, 'surat_urutan' => 4, 'ayat_mulai' => 148, 'ayat_selesai' => 176],  // An-Nisa
        ['juz' => 6, 'surat_urutan' => 5, 'ayat_mulai' => 1, 'ayat_selesai' => 81],     // Al-Ma'idah

        // Juz 7
        ['juz' => 7, 'surat_urutan' => 5, 'ayat_mulai' => 82, 'ayat_selesai' => 120],   // Al-Ma'idah
        ['juz' => 7, 'surat_urutan' => 6, 'ayat_mulai' => 1, 'ayat_selesai' => 110],     // Al-An'am

        // Juz 8
        ['juz' => 8, 'surat_urutan' => 6, 'ayat_mulai' => 111, 'ayat_selesai' => 165],  // Al-An'am
        ['juz' => 8, 'surat_urutan' => 7, 'ayat_mulai' => 1, 'ayat_selesai' => 87],     // Al-A'raf

        // Juz 9
        ['juz' => 9, 'surat_urutan' => 7, 'ayat_mulai' => 88, 'ayat_selesai' => 206],    // Al-A'raf
        ['juz' => 9, 'surat_urutan' => 8, 'ayat_mulai' => 1, 'ayat_selesai' => 40],     // Al-Anfal

        // Juz 10
        ['juz' => 10, 'surat_urutan' => 8, 'ayat_mulai' => 41, 'ayat_selesai' => 75],    // Al-Anfal
        ['juz' => 10, 'surat_urutan' => 9, 'ayat_mulai' => 1, 'ayat_selesai' => 92],     // At-Taubah

        // Juz 11
        ['juz' => 11, 'surat_urutan' => 9, 'ayat_mulai' => 93, 'ayat_selesai' => 129],   // At-Taubah
        ['juz' => 11, 'surat_urutan' => 10, 'ayat_mulai' => 1, 'ayat_selesai' => 109],    // Yunus

        // Juz 12
        ['juz' => 12, 'surat_urutan' => 10, 'ayat_mulai' => 110, 'ayat_selesai' => 123],  // Yunus
        ['juz' => 12, 'surat_urutan' => 11, 'ayat_mulai' => 1, 'ayat_selesai' => 123],    // Hud
        ['juz' => 12, 'surat_urutan' => 12, 'ayat_mulai' => 1, 'ayat_selesai' => 52],     // Yusuf

        // Juz 13
        ['juz' => 13, 'surat_urutan' => 12, 'ayat_mulai' => 53, 'ayat_selesai' => 111],  // Yusuf
        ['juz' => 13, 'surat_urutan' => 13, 'ayat_mulai' => 1, 'ayat_selesai' => 43],     // Ar-Ra'd
        ['juz' => 13, 'surat_urutan' => 14, 'ayat_mulai' => 1, 'ayat_selesai' => 52],     // Ibrahim

        // Juz 14
        ['juz' => 14, 'surat_urutan' => 15, 'ayat_mulai' => 1, 'ayat_selesai' => 99],     // Al-Hijr
        ['juz' => 14, 'surat_urutan' => 16, 'ayat_mulai' => 1, 'ayat_selesai' => 128],    // An-Nahl

        // Juz 15
        ['juz' => 15, 'surat_urutan' => 17, 'ayat_mulai' => 1, 'ayat_selesai' => 111],    // Al-Isra
        ['juz' => 15, 'surat_urutan' => 18, 'ayat_mulai' => 1, 'ayat_selesai' => 74],     // Al-Kahf

        // Juz 16
        ['juz' => 16, 'surat_urutan' => 18, 'ayat_mulai' => 75, 'ayat_selesai' => 110],    // Al-Kahf
        ['juz' => 16, 'surat_urutan' => 19, 'ayat_mulai' => 1, 'ayat_selesai' => 98],     // Maryam
        ['juz' => 16, 'surat_urutan' => 20, 'ayat_mulai' => 1, 'ayat_selesai' => 135],    // Taha

        // Juz 17
        ['juz' => 17, 'surat_urutan' => 21, 'ayat_mulai' => 1, 'ayat_selesai' => 112],    // Al-Anbiya
        ['juz' => 17, 'surat_urutan' => 22, 'ayat_mulai' => 1, 'ayat_selesai' => 78],     // Al-Hajj

        // Juz 18
        ['juz' => 18, 'surat_urutan' => 23, 'ayat_mulai' => 1, 'ayat_selesai' => 118],    // Al-Mu'minun
        ['juz' => 18, 'surat_urutan' => 24, 'ayat_mulai' => 1, 'ayat_selesai' => 64],     // An-Nur
        ['juz' => 18, 'surat_urutan' => 25, 'ayat_mulai' => 1, 'ayat_selesai' => 20],     // Al-Furqan

        // Juz 19
        ['juz' => 19, 'surat_urutan' => 25, 'ayat_mulai' => 21, 'ayat_selesai' => 77],    // Al-Furqan
        ['juz' => 19, 'surat_urutan' => 26, 'ayat_mulai' => 1, 'ayat_selesai' => 227],    // Asy-Syu'ara
        ['juz' => 19, 'surat_urutan' => 27, 'ayat_mulai' => 1, 'ayat_selesai' => 55],     // An-Naml

        // Juz 20
        ['juz' => 20, 'surat_urutan' => 27, 'ayat_mulai' => 56, 'ayat_selesai' => 93],    // An-Naml
        ['juz' => 20, 'surat_urutan' => 28, 'ayat_mulai' => 1, 'ayat_selesai' => 88],     // Al-Qasas
        ['juz' => 20, 'surat_urutan' => 29, 'ayat_mulai' => 1, 'ayat_selesai' => 45],     // Al-'Ankabut

        // Juz 21
        ['juz' => 21, 'surat_urutan' => 29, 'ayat_mulai' => 46, 'ayat_selesai' => 69],    // Al-'Ankabut
        ['juz' => 21, 'surat_urutan' => 30, 'ayat_mulai' => 1, 'ayat_selesai' => 60],     // Ar-Rum
        ['juz' => 21, 'surat_urutan' => 31, 'ayat_mulai' => 1, 'ayat_selesai' => 34],     // Luqman
        ['juz' => 21, 'surat_urutan' => 32, 'ayat_mulai' => 1, 'ayat_selesai' => 30],     // As-Sajdah
        ['juz' => 21, 'surat_urutan' => 33, 'ayat_mulai' => 1, 'ayat_selesai' => 30],     // Al-Ahzab

        // Juz 22
        ['juz' => 22, 'surat_urutan' => 33, 'ayat_mulai' => 31, 'ayat_selesai' => 73],    // Al-Ahzab
        ['juz' => 22, 'surat_urutan' => 34, 'ayat_mulai' => 1, 'ayat_selesai' => 54],     // Saba
        ['juz' => 22, 'surat_urutan' => 35, 'ayat_mulai' => 1, 'ayat_selesai' => 45],     // Fatir
        ['juz' => 22, 'surat_urutan' => 36, 'ayat_mulai' => 1, 'ayat_selesai' => 27],     // Yasin

        // Juz 23
        ['juz' => 23, 'surat_urutan' => 36, 'ayat_mulai' => 28, 'ayat_selesai' => 83],    // Yasin
        ['juz' => 23, 'surat_urutan' => 37, 'ayat_mulai' => 1, 'ayat_selesai' => 182],    // As-Saffat
        ['juz' => 23, 'surat_urutan' => 38, 'ayat_mulai' => 1, 'ayat_selesai' => 88],     // Sad

        // Juz 24
        ['juz' => 24, 'surat_urutan' => 39, 'ayat_mulai' => 1, 'ayat_selesai' => 75],     // Az-Zumar
        ['juz' => 24, 'surat_urutan' => 40, 'ayat_mulai' => 1, 'ayat_selesai' => 85],     // Al-Mu'min
        ['juz' => 24, 'surat_urutan' => 41, 'ayat_mulai' => 1, 'ayat_selesai' => 46],     // Fussilat
        ['juz' => 24, 'surat_urutan' => 42, 'ayat_mulai' => 1, 'ayat_selesai' => 53],     // Asy-Syura

        // Juz 25
        ['juz' => 25, 'surat_urutan' => 43, 'ayat_mulai' => 1, 'ayat_selesai' => 89],     // Az-Zukhruf
        ['juz' => 25, 'surat_urutan' => 44, 'ayat_mulai' => 1, 'ayat_selesai' => 59],     // Ad-Dukhan
        ['juz' => 25, 'surat_urutan' => 45, 'ayat_mulai' => 1, 'ayat_selesai' => 37],     // Al-Jasiyah
        ['juz' => 25, 'surat_urutan' => 46, 'ayat_mulai' => 1, 'ayat_selesai' => 35],     // Al-Ahqaf

        // Juz 26
        ['juz' => 26, 'surat_urutan' => 47, 'ayat_mulai' => 1, 'ayat_selesai' => 38],     // Muhammad
        ['juz' => 26, 'surat_urutan' => 48, 'ayat_mulai' => 1, 'ayat_selesai' => 29],     // Al-Fath
        ['juz' => 26, 'surat_urutan' => 49, 'ayat_mulai' => 1, 'ayat_selesai' => 18],     // Al-Hujurat
        ['juz' => 26, 'surat_urutan' => 50, 'ayat_mulai' => 1, 'ayat_selesai' => 45],     // Qaf
        ['juz' => 26, 'surat_urutan' => 51, 'ayat_mulai' => 1, 'ayat_selesai' => 60],     // Adz-Dzariyat

        // Juz 27
        ['juz' => 27, 'surat_urutan' => 52, 'ayat_mulai' => 1, 'ayat_selesai' => 49],     // At-Tur
        ['juz' => 27, 'surat_urutan' => 53, 'ayat_mulai' => 1, 'ayat_selesai' => 62],     // An-Najm
        ['juz' => 27, 'surat_urutan' => 54, 'ayat_mulai' => 1, 'ayat_selesai' => 55],     // Al-Qamar
        ['juz' => 27, 'surat_urutan' => 55, 'ayat_mulai' => 1, 'ayat_selesai' => 78],     // Ar-Rahman
        ['juz' => 27, 'surat_urutan' => 56, 'ayat_mulai' => 1, 'ayat_selesai' => 96],     // Al-Waqi'ah

        // Juz 28
        ['juz' => 28, 'surat_urutan' => 57, 'ayat_mulai' => 1, 'ayat_selesai' => 29],     // Al-Hadid
        ['juz' => 28, 'surat_urutan' => 58, 'ayat_mulai' => 1, 'ayat_selesai' => 22],     // Al-Mujadilah
        ['juz' => 28, 'surat_urutan' => 59, 'ayat_mulai' => 1, 'ayat_selesai' => 24],     // Al-Hasyr
        ['juz' => 28, 'surat_urutan' => 60, 'ayat_mulai' => 1, 'ayat_selesai' => 13],     // Al-Mumtahanah
        ['juz' => 28, 'surat_urutan' => 61, 'ayat_mulai' => 1, 'ayat_selesai' => 14],     // As-Saff
        ['juz' => 28, 'surat_urutan' => 62, 'ayat_mulai' => 1, 'ayat_selesai' => 11],     // Al-Jumu'ah
        ['juz' => 28, 'surat_urutan' => 63, 'ayat_mulai' => 1, 'ayat_selesai' => 11],     // Al-Munafiqun
        ['juz' => 28, 'surat_urutan' => 64, 'ayat_mulai' => 1, 'ayat_selesai' => 18],     // At-Tagabun

        // Juz 29
        ['juz' => 29, 'surat_urutan' => 65, 'ayat_mulai' => 1, 'ayat_selesai' => 12],     // At-Talaq
        ['juz' => 29, 'surat_urutan' => 66, 'ayat_mulai' => 1, 'ayat_selesai' => 12],     // At-Tahrim
        ['juz' => 29, 'surat_urutan' => 67, 'ayat_mulai' => 1, 'ayat_selesai' => 30],     // Al-Mulk
        ['juz' => 29, 'surat_urutan' => 68, 'ayat_mulai' => 1, 'ayat_selesai' => 52],     // Al-Qalam
        ['juz' => 29, 'surat_urutan' => 69, 'ayat_mulai' => 1, 'ayat_selesai' => 52],     // Al-Haqqah
        ['juz' => 29, 'surat_urutan' => 70, 'ayat_mulai' => 1, 'ayat_selesai' => 44],     // Al-Ma'arij
        ['juz' => 29, 'surat_urutan' => 71, 'ayat_mulai' => 1, 'ayat_selesai' => 28],     // Nuh
        ['juz' => 29, 'surat_urutan' => 72, 'ayat_mulai' => 1, 'ayat_selesai' => 28],     // Al-Jinn
        ['juz' => 29, 'surat_urutan' => 73, 'ayat_mulai' => 1, 'ayat_selesai' => 20],     // Al-Muzzammil
        ['juz' => 29, 'surat_urutan' => 74, 'ayat_mulai' => 1, 'ayat_selesai' => 56],     // Al-Muddassir

        // Juz 30
        ['juz' => 30, 'surat_urutan' => 75, 'ayat_mulai' => 1, 'ayat_selesai' => 40],     // Al-Qiyamah
        ['juz' => 30, 'surat_urutan' => 76, 'ayat_mulai' => 1, 'ayat_selesai' => 31],     // Al-Insan
        ['juz' => 30, 'surat_urutan' => 77, 'ayat_mulai' => 1, 'ayat_selesai' => 50],     // Al-Mursalat
        ['juz' => 30, 'surat_urutan' => 78, 'ayat_mulai' => 1, 'ayat_selesai' => 40],     // An-Naba
        ['juz' => 30, 'surat_urutan' => 79, 'ayat_mulai' => 1, 'ayat_selesai' => 46],     // An-Nazi'at
        ['juz' => 30, 'surat_urutan' => 80, 'ayat_mulai' => 1, 'ayat_selesai' => 42],     // Abasa
        ['juz' => 30, 'surat_urutan' => 81, 'ayat_mulai' => 1, 'ayat_selesai' => 29],     // At-Takwir
        ['juz' => 30, 'surat_urutan' => 82, 'ayat_mulai' => 1, 'ayat_selesai' => 19],     // Al-Infitar
        ['juz' => 30, 'surat_urutan' => 83, 'ayat_mulai' => 1, 'ayat_selesai' => 36],     // Al-Mutaffifin
        ['juz' => 30, 'surat_urutan' => 84, 'ayat_mulai' => 1, 'ayat_selesai' => 25],     // Al-Insiqaq
        ['juz' => 30, 'surat_urutan' => 85, 'ayat_mulai' => 1, 'ayat_selesai' => 22],     // Al-Buruj
        ['juz' => 30, 'surat_urutan' => 86, 'ayat_mulai' => 1, 'ayat_selesai' => 17],     // At-Tariq
        ['juz' => 30, 'surat_urutan' => 87, 'ayat_mulai' => 1, 'ayat_selesai' => 19],     // Al-A'la
        ['juz' => 30, 'surat_urutan' => 88, 'ayat_mulai' => 1, 'ayat_selesai' => 26],     // Al-Gasyiyah
        ['juz' => 30, 'surat_urutan' => 89, 'ayat_mulai' => 1, 'ayat_selesai' => 30],     // Al-Fajr
        ['juz' => 30, 'surat_urutan' => 90, 'ayat_mulai' => 1, 'ayat_selesai' => 20],     // Al-Balad
        ['juz' => 30, 'surat_urutan' => 91, 'ayat_mulai' => 1, 'ayat_selesai' => 15],     // Asy-Syams
        ['juz' => 30, 'surat_urutan' => 92, 'ayat_mulai' => 1, 'ayat_selesai' => 21],     // Al-Lail
        ['juz' => 30, 'surat_urutan' => 93, 'ayat_mulai' => 1, 'ayat_selesai' => 11],     // Ad-Duha
        ['juz' => 30, 'surat_urutan' => 94, 'ayat_mulai' => 1, 'ayat_selesai' => 8],      // Asy-Syarh
        ['juz' => 30, 'surat_urutan' => 95, 'ayat_mulai' => 1, 'ayat_selesai' => 8],      // At-Tin
        ['juz' => 30, 'surat_urutan' => 96, 'ayat_mulai' => 1, 'ayat_selesai' => 19],     // Al-'Alaq
        ['juz' => 30, 'surat_urutan' => 97, 'ayat_mulai' => 1, 'ayat_selesai' => 5],      // Al-Qadr
        ['juz' => 30, 'surat_urutan' => 98, 'ayat_mulai' => 1, 'ayat_selesai' => 8],      // Al-Bayyinah
        ['juz' => 30, 'surat_urutan' => 99, 'ayat_mulai' => 1, 'ayat_selesai' => 8],      // Az-Zalzalah
        ['juz' => 30, 'surat_urutan' => 100, 'ayat_mulai' => 1, 'ayat_selesai' => 11],    // Al-'Adiyat
        ['juz' => 30, 'surat_urutan' => 101, 'ayat_mulai' => 1, 'ayat_selesai' => 11],    // Al-Qari'ah
        ['juz' => 30, 'surat_urutan' => 102, 'ayat_mulai' => 1, 'ayat_selesai' => 8],     // At-Takasur
        ['juz' => 30, 'surat_urutan' => 103, 'ayat_mulai' => 1, 'ayat_selesai' => 3],     // Al-'Asr
        ['juz' => 30, 'surat_urutan' => 104, 'ayat_mulai' => 1, 'ayat_selesai' => 9],     // Al-Humazah
        ['juz' => 30, 'surat_urutan' => 105, 'ayat_mulai' => 1, 'ayat_selesai' => 5],     // Al-Fil
        ['juz' => 30, 'surat_urutan' => 106, 'ayat_mulai' => 1, 'ayat_selesai' => 4],     // Quraisy
        ['juz' => 30, 'surat_urutan' => 107, 'ayat_mulai' => 1, 'ayat_selesai' => 7],     // Al-Ma'un
        ['juz' => 30, 'surat_urutan' => 108, 'ayat_mulai' => 1, 'ayat_selesai' => 3],     // Al-Kausar
        ['juz' => 30, 'surat_urutan' => 109, 'ayat_mulai' => 1, 'ayat_selesai' => 6],     // Al-Kafirun
        ['juz' => 30, 'surat_urutan' => 110, 'ayat_mulai' => 1, 'ayat_selesai' => 3],     // An-Nasr
        ['juz' => 30, 'surat_urutan' => 111, 'ayat_mulai' => 1, 'ayat_selesai' => 5],     // Al-Lahab
        ['juz' => 30, 'surat_urutan' => 112, 'ayat_mulai' => 1, 'ayat_selesai' => 4],     // Al-Ikhlas
        ['juz' => 30, 'surat_urutan' => 113, 'ayat_mulai' => 1, 'ayat_selesai' => 5],     // Al-Falaq
        ['juz' => 30, 'surat_urutan' => 114, 'ayat_mulai' => 1, 'ayat_selesai' => 6],     // An-Nas
    ];

    public function run(): void
    {
        $suratMap = Surat::orderBy('urutan')->pluck('id', 'urutan');

        $rows = [];
        foreach ($this->mapping as $m) {
            $suratId = $suratMap[$m['surat_urutan']] ?? null;
            if (! $suratId) {
                $this->command->warn("Surat urutan {$m['surat_urutan']} tidak ditemukan, skip.");

                continue;
            }

            $rows[] = [
                'juz' => $m['juz'],
                'surat_id' => $suratId,
                'ayat_mulai' => $m['ayat_mulai'],
                'ayat_selesai' => $m['ayat_selesai'],
                'total_ayat' => $m['ayat_selesai'] - $m['ayat_mulai'] + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (! empty($rows)) {
            DB::table('juz_surats')->insert($rows);
        }

        $this->command->info('Mapping Juz-Surat berhasil di-seed: '.count($rows).' baris.');
    }
}
