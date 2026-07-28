<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuratSeeder extends Seeder
{
    public function run(): void
    {
        // Cek apakah sudah ada data — hindari duplikat
        if (DB::table('surats')->count() > 0) {
            $this->command->info('Surat sudah ada, skip seeding.');
            return;
        }

        $surats = [
            ['nama' => 'Al-Fatihah', 'nama_latin' => 'Al-Fatihah', 'jumlah_ayat' => 7, 'jenis' => 'Makkiyah', 'urutan' => 1],
            ['nama' => 'Al-Baqarah', 'nama_latin' => 'Al-Baqarah', 'jumlah_ayat' => 286, 'jenis' => 'Madaniyah', 'urutan' => 2],
            ['nama' => 'Ali Imran', 'nama_latin' => "Ali 'Imran", 'jumlah_ayat' => 200, 'jenis' => 'Madaniyah', 'urutan' => 3],
            ['nama' => "An-Nisa'", 'nama_latin' => "An-Nisa'", 'jumlah_ayat' => 176, 'jenis' => 'Madaniyah', 'urutan' => 4],
            ['nama' => "Al-Ma'idah", 'nama_latin' => "Al-Ma'idah", 'jumlah_ayat' => 120, 'jenis' => 'Madaniyah', 'urutan' => 5],
            ['nama' => "Al-An'am", 'nama_latin' => "Al-An'am", 'jumlah_ayat' => 165, 'jenis' => 'Makkiyah', 'urutan' => 6],
            ['nama' => "Al-A'raf", 'nama_latin' => "Al-A'raf", 'jumlah_ayat' => 206, 'jenis' => 'Makkiyah', 'urutan' => 7],
            ['nama' => 'Al-Anfal', 'nama_latin' => 'Al-Anfal', 'jumlah_ayat' => 75, 'jenis' => 'Madaniyah', 'urutan' => 8],
            ['nama' => 'At-Taubah', 'nama_latin' => 'At-Taubah', 'jumlah_ayat' => 129, 'jenis' => 'Madaniyah', 'urutan' => 9],
            ['nama' => 'Yunus', 'nama_latin' => 'Yunus', 'jumlah_ayat' => 109, 'jenis' => 'Makkiyah', 'urutan' => 10],
            ['nama' => 'Hud', 'nama_latin' => 'Hud', 'jumlah_ayat' => 123, 'jenis' => 'Makkiyah', 'urutan' => 11],
            ['nama' => 'Yusuf', 'nama_latin' => 'Yusuf', 'jumlah_ayat' => 111, 'jenis' => 'Makkiyah', 'urutan' => 12],
            ['nama' => "Ar-Ra'd", 'nama_latin' => "Ar-Ra'd", 'jumlah_ayat' => 43, 'jenis' => 'Madaniyah', 'urutan' => 13],
            ['nama' => 'Ibrahim', 'nama_latin' => 'Ibrahim', 'jumlah_ayat' => 52, 'jenis' => 'Makkiyah', 'urutan' => 14],
            ['nama' => 'Al-Hijr', 'nama_latin' => 'Al-Hijr', 'jumlah_ayat' => 99, 'jenis' => 'Makkiyah', 'urutan' => 15],
            ['nama' => 'An-Nahl', 'nama_latin' => 'An-Nahl', 'jumlah_ayat' => 128, 'jenis' => 'Makkiyah', 'urutan' => 16],
            ['nama' => 'Al-Isra', 'nama_latin' => "Al-Isra'", 'jumlah_ayat' => 111, 'jenis' => 'Makkiyah', 'urutan' => 17],
            ['nama' => 'Al-Kahf', 'nama_latin' => 'Al-Kahf', 'jumlah_ayat' => 110, 'jenis' => 'Makkiyah', 'urutan' => 18],
            ['nama' => 'Maryam', 'nama_latin' => 'Maryam', 'jumlah_ayat' => 98, 'jenis' => 'Makkiyah', 'urutan' => 19],
            ['nama' => 'Taha', 'nama_latin' => 'Taha', 'jumlah_ayat' => 135, 'jenis' => 'Makkiyah', 'urutan' => 20],
            ['nama' => 'Al-Anbiya', 'nama_latin' => "Al-Anbiya'", 'jumlah_ayat' => 112, 'jenis' => 'Makkiyah', 'urutan' => 21],
            ['nama' => 'Al-Hajj', 'nama_latin' => 'Al-Hajj', 'jumlah_ayat' => 78, 'jenis' => 'Madaniyah', 'urutan' => 22],
            ['nama' => "Al-Mu'minun", 'nama_latin' => "Al-Mu'minun", 'jumlah_ayat' => 118, 'jenis' => 'Makkiyah', 'urutan' => 23],
            ['nama' => 'An-Nur', 'nama_latin' => 'An-Nur', 'jumlah_ayat' => 64, 'jenis' => 'Madaniyah', 'urutan' => 24],
            ['nama' => 'Al-Furqan', 'nama_latin' => 'Al-Furqan', 'jumlah_ayat' => 77, 'jenis' => 'Makkiyah', 'urutan' => 25],
            ['nama' => "Asy-Syu'ara'", 'nama_latin' => "Asy-Syu'ara'", 'jumlah_ayat' => 227, 'jenis' => 'Makkiyah', 'urutan' => 26],
            ['nama' => 'An-Naml', 'nama_latin' => 'An-Naml', 'jumlah_ayat' => 93, 'jenis' => 'Makkiyah', 'urutan' => 27],
            ['nama' => 'Al-Qasas', 'nama_latin' => 'Al-Qasas', 'jumlah_ayat' => 88, 'jenis' => 'Makkiyah', 'urutan' => 28],
            ['nama' => "Al-'Ankabut", 'nama_latin' => "Al-'Ankabut", 'jumlah_ayat' => 69, 'jenis' => 'Makkiyah', 'urutan' => 29],
            ['nama' => 'Ar-Rum', 'nama_latin' => 'Ar-Rum', 'jumlah_ayat' => 60, 'jenis' => 'Makkiyah', 'urutan' => 30],
            ['nama' => 'Luqman', 'nama_latin' => 'Luqman', 'jumlah_ayat' => 34, 'jenis' => 'Makkiyah', 'urutan' => 31],
            ['nama' => 'As-Sajdah', 'nama_latin' => 'As-Sajdah', 'jumlah_ayat' => 30, 'jenis' => 'Makkiyah', 'urutan' => 32],
            ['nama' => 'Al-Ahzab', 'nama_latin' => 'Al-Ahzab', 'jumlah_ayat' => 73, 'jenis' => 'Madaniyah', 'urutan' => 33],
            ['nama' => 'Saba', 'nama_latin' => 'Saba\'', 'jumlah_ayat' => 54, 'jenis' => 'Makkiyah', 'urutan' => 34],
            ['nama' => 'Fatir', 'nama_latin' => 'Fatir', 'jumlah_ayat' => 45, 'jenis' => 'Makkiyah', 'urutan' => 35],
            ['nama' => 'Yasin', 'nama_latin' => 'Yasin', 'jumlah_ayat' => 83, 'jenis' => 'Makkiyah', 'urutan' => 36],
            ['nama' => 'As-Saffat', 'nama_latin' => 'As-Saffat', 'jumlah_ayat' => 182, 'jenis' => 'Makkiyah', 'urutan' => 37],
            ['nama' => 'Sad', 'nama_latin' => 'Sad', 'jumlah_ayat' => 88, 'jenis' => 'Makkiyah', 'urutan' => 38],
            ['nama' => 'Az-Zumar', 'nama_latin' => 'Az-Zumar', 'jumlah_ayat' => 75, 'jenis' => 'Makkiyah', 'urutan' => 39],
            ['nama' => 'Al-Mu\'min', 'nama_latin' => 'Al-Mu\'min', 'jumlah_ayat' => 85, 'jenis' => 'Makkiyah', 'urutan' => 40],
            ['nama' => 'Fussilat', 'nama_latin' => 'Fussilat', 'jumlah_ayat' => 54, 'jenis' => 'Makkiyah', 'urutan' => 41],
            ['nama' => 'Asy-Syura', 'nama_latin' => 'Asy-Syura', 'jumlah_ayat' => 53, 'jenis' => 'Makkiyah', 'urutan' => 42],
            ['nama' => 'Az-Zukhruf', 'nama_latin' => 'Az-Zukhruf', 'jumlah_ayat' => 89, 'jenis' => 'Makkiyah', 'urutan' => 43],
            ['nama' => 'Ad-Dukhan', 'nama_latin' => 'Ad-Dukhan', 'jumlah_ayat' => 59, 'jenis' => 'Makkiyah', 'urutan' => 44],
            ['nama' => 'Al-Jasiyah', 'nama_latin' => 'Al-Jasiyah', 'jumlah_ayat' => 37, 'jenis' => 'Makkiyah', 'urutan' => 45],
            ['nama' => 'Al-Ahqaf', 'nama_latin' => 'Al-Ahqaf', 'jumlah_ayat' => 35, 'jenis' => 'Makkiyah', 'urutan' => 46],
            ['nama' => 'Muhammad', 'nama_latin' => 'Muhammad', 'jumlah_ayat' => 38, 'jenis' => 'Madaniyah', 'urutan' => 47],
            ['nama' => 'Al-Fath', 'nama_latin' => 'Al-Fath', 'jumlah_ayat' => 29, 'jenis' => 'Madaniyah', 'urutan' => 48],
            ['nama' => 'Al-Hujurat', 'nama_latin' => 'Al-Hujurat', 'jumlah_ayat' => 18, 'jenis' => 'Madaniyah', 'urutan' => 49],
            ['nama' => 'Qaf', 'nama_latin' => 'Qaf', 'jumlah_ayat' => 45, 'jenis' => 'Makkiyah', 'urutan' => 50],
            ['nama' => 'Adz-Dzariyat', 'nama_latin' => 'Adz-Dzariyat', 'jumlah_ayat' => 60, 'jenis' => 'Makkiyah', 'urutan' => 51],
            ['nama' => 'At-Tur', 'nama_latin' => 'At-Tur', 'jumlah_ayat' => 49, 'jenis' => 'Makkiyah', 'urutan' => 52],
            ['nama' => 'An-Najm', 'nama_latin' => 'An-Najm', 'jumlah_ayat' => 62, 'jenis' => 'Makkiyah', 'urutan' => 53],
            ['nama' => 'Al-Qamar', 'nama_latin' => 'Al-Qamar', 'jumlah_ayat' => 55, 'jenis' => 'Makkiyah', 'urutan' => 54],
            ['nama' => 'Ar-Rahman', 'nama_latin' => 'Ar-Rahman', 'jumlah_ayat' => 78, 'jenis' => 'Madaniyah', 'urutan' => 55],
            ['nama' => 'Al-Waqi\'ah', 'nama_latin' => 'Al-Waqi\'ah', 'jumlah_ayat' => 96, 'jenis' => 'Makkiyah', 'urutan' => 56],
            ['nama' => 'Al-Hadid', 'nama_latin' => 'Al-Hadid', 'jumlah_ayat' => 29, 'jenis' => 'Madaniyah', 'urutan' => 57],
            ['nama' => 'Al-Mujadilah', 'nama_latin' => 'Al-Mujadilah', 'jumlah_ayat' => 22, 'jenis' => 'Madaniyah', 'urutan' => 58],
            ['nama' => 'Al-Hasyr', 'nama_latin' => 'Al-Hasyr', 'jumlah_ayat' => 24, 'jenis' => 'Madaniyah', 'urutan' => 59],
            ['nama' => 'Al-Mumtahanah', 'nama_latin' => 'Al-Mumtahanah', 'jumlah_ayat' => 13, 'jenis' => 'Madaniyah', 'urutan' => 60],
            ['nama' => 'As-Saff', 'nama_latin' => 'As-Saff', 'jumlah_ayat' => 14, 'jenis' => 'Madaniyah', 'urutan' => 61],
            ['nama' => "Al-Jumu'ah", 'nama_latin' => "Al-Jumu'ah", 'jumlah_ayat' => 11, 'jenis' => 'Madaniyah', 'urutan' => 62],
            ['nama' => 'Al-Munafiqun', 'nama_latin' => 'Al-Munafiqun', 'jumlah_ayat' => 11, 'jenis' => 'Madaniyah', 'urutan' => 63],
            ['nama' => 'At-Tagabun', 'nama_latin' => 'At-Tagabun', 'jumlah_ayat' => 18, 'jenis' => 'Madaniyah', 'urutan' => 64],
            ['nama' => 'At-Talaq', 'nama_latin' => 'At-Talaq', 'jumlah_ayat' => 12, 'jenis' => 'Madaniyah', 'urutan' => 65],
            ['nama' => 'At-Tahrim', 'nama_latin' => 'At-Tahrim', 'jumlah_ayat' => 12, 'jenis' => 'Madaniyah', 'urutan' => 66],
            ['nama' => 'Al-Mulk', 'nama_latin' => 'Al-Mulk', 'jumlah_ayat' => 30, 'jenis' => 'Makkiyah', 'urutan' => 67],
            ['nama' => 'Al-Qalam', 'nama_latin' => 'Al-Qalam', 'jumlah_ayat' => 52, 'jenis' => 'Makkiyah', 'urutan' => 68],
            ['nama' => 'Al-Haqqah', 'nama_latin' => 'Al-Haqqah', 'jumlah_ayat' => 52, 'jenis' => 'Makkiyah', 'urutan' => 69],
            ['nama' => "Al-Ma'arij", 'nama_latin' => "Al-Ma'arij", 'jumlah_ayat' => 44, 'jenis' => 'Makkiyah', 'urutan' => 70],
            ['nama' => 'Nuh', 'nama_latin' => 'Nuh', 'jumlah_ayat' => 28, 'jenis' => 'Makkiyah', 'urutan' => 71],
            ['nama' => 'Al-Jinn', 'nama_latin' => 'Al-Jinn', 'jumlah_ayat' => 28, 'jenis' => 'Makkiyah', 'urutan' => 72],
            ['nama' => 'Al-Muzzammil', 'nama_latin' => 'Al-Muzzammil', 'jumlah_ayat' => 20, 'jenis' => 'Makkiyah', 'urutan' => 73],
            ['nama' => 'Al-Muddassir', 'nama_latin' => 'Al-Muddassir', 'jumlah_ayat' => 56, 'jenis' => 'Makkiyah', 'urutan' => 74],
            ['nama' => 'Al-Qiyamah', 'nama_latin' => 'Al-Qiyamah', 'jumlah_ayat' => 40, 'jenis' => 'Makkiyah', 'urutan' => 75],
            ['nama' => 'Al-Insan', 'nama_latin' => 'Al-Insan', 'jumlah_ayat' => 31, 'jenis' => 'Madaniyah', 'urutan' => 76],
            ['nama' => 'Al-Mursalat', 'nama_latin' => 'Al-Mursalat', 'jumlah_ayat' => 50, 'jenis' => 'Makkiyah', 'urutan' => 77],
            ['nama' => 'An-Naba', 'nama_latin' => 'An-Naba\'', 'jumlah_ayat' => 40, 'jenis' => 'Makkiyah', 'urutan' => 78],
            ['nama' => "An-Nazi'at", 'nama_latin' => "An-Nazi'at", 'jumlah_ayat' => 46, 'jenis' => 'Makkiyah', 'urutan' => 79],
            ['nama' => 'Abasa', 'nama_latin' => 'Abasa', 'jumlah_ayat' => 42, 'jenis' => 'Makkiyah', 'urutan' => 80],
            ['nama' => 'At-Takwir', 'nama_latin' => 'At-Takwir', 'jumlah_ayat' => 29, 'jenis' => 'Makkiyah', 'urutan' => 81],
            ['nama' => 'Al-Infitar', 'nama_latin' => 'Al-Infitar', 'jumlah_ayat' => 19, 'jenis' => 'Makkiyah', 'urutan' => 82],
            ['nama' => 'Al-Mutaffifin', 'nama_latin' => 'Al-Mutaffifin', 'jumlah_ayat' => 36, 'jenis' => 'Makkiyah', 'urutan' => 83],
            ['nama' => 'Al-Insiqaq', 'nama_latin' => 'Al-Insiqaq', 'jumlah_ayat' => 25, 'jenis' => 'Makkiyah', 'urutan' => 84],
            ['nama' => 'Al-Buruj', 'nama_latin' => 'Al-Buruj', 'jumlah_ayat' => 22, 'jenis' => 'Makkiyah', 'urutan' => 85],
            ['nama' => 'At-Tariq', 'nama_latin' => 'At-Tariq', 'jumlah_ayat' => 17, 'jenis' => 'Makkiyah', 'urutan' => 86],
            ['nama' => "Al-A'la", 'nama_latin' => "Al-A'la", 'jumlah_ayat' => 19, 'jenis' => 'Makkiyah', 'urutan' => 87],
            ['nama' => 'Al-Gasyiyah', 'nama_latin' => 'Al-Gasyiyah', 'jumlah_ayat' => 26, 'jenis' => 'Makkiyah', 'urutan' => 88],
            ['nama' => 'Al-Fajr', 'nama_latin' => 'Al-Fajr', 'jumlah_ayat' => 30, 'jenis' => 'Makkiyah', 'urutan' => 89],
            ['nama' => 'Al-Balad', 'nama_latin' => 'Al-Balad', 'jumlah_ayat' => 20, 'jenis' => 'Makkiyah', 'urutan' => 90],
            ['nama' => 'Asy-Syams', 'nama_latin' => 'Asy-Syams', 'jumlah_ayat' => 15, 'jenis' => 'Makkiyah', 'urutan' => 91],
            ['nama' => 'Al-Lail', 'nama_latin' => 'Al-Lail', 'jumlah_ayat' => 21, 'jenis' => 'Makkiyah', 'urutan' => 92],
            ['nama' => 'Ad-Duha', 'nama_latin' => 'Ad-Duha', 'jumlah_ayat' => 11, 'jenis' => 'Makkiyah', 'urutan' => 93],
            ['nama' => 'Asy-Syarh', 'nama_latin' => 'Asy-Syarh', 'jumlah_ayat' => 8, 'jenis' => 'Makkiyah', 'urutan' => 94],
            ['nama' => 'At-Tin', 'nama_latin' => 'At-Tin', 'jumlah_ayat' => 8, 'jenis' => 'Makkiyah', 'urutan' => 95],
            ['nama' => "Al-'Alaq", 'nama_latin' => "Al-'Alaq", 'jumlah_ayat' => 19, 'jenis' => 'Makkiyah', 'urutan' => 96],
            ['nama' => 'Al-Qadr', 'nama_latin' => 'Al-Qadr', 'jumlah_ayat' => 5, 'jenis' => 'Makkiyah', 'urutan' => 97],
            ['nama' => 'Al-Bayyinah', 'nama_latin' => 'Al-Bayyinah', 'jumlah_ayat' => 8, 'jenis' => 'Madaniyah', 'urutan' => 98],
            ['nama' => 'Az-Zalzalah', 'nama_latin' => 'Az-Zalzalah', 'jumlah_ayat' => 8, 'jenis' => 'Madaniyah', 'urutan' => 99],
            ['nama' => "Al-'Adiyat", 'nama_latin' => "Al-'Adiyat", 'jumlah_ayat' => 11, 'jenis' => 'Makkiyah', 'urutan' => 100],
            ['nama' => 'Al-Qari\'ah', 'nama_latin' => 'Al-Qari\'ah', 'jumlah_ayat' => 11, 'jenis' => 'Makkiyah', 'urutan' => 101],
            ['nama' => 'At-Takasur', 'nama_latin' => 'At-Takasur', 'jumlah_ayat' => 8, 'jenis' => 'Makkiyah', 'urutan' => 102],
            ['nama' => "Al-'Asr", 'nama_latin' => "Al-'Asr", 'jumlah_ayat' => 3, 'jenis' => 'Makkiyah', 'urutan' => 103],
            ['nama' => 'Al-Humazah', 'nama_latin' => 'Al-Humazah', 'jumlah_ayat' => 9, 'jenis' => 'Makkiyah', 'urutan' => 104],
            ['nama' => 'Al-Fil', 'nama_latin' => 'Al-Fil', 'jumlah_ayat' => 5, 'jenis' => 'Makkiyah', 'urutan' => 105],
            ['nama' => 'Quraisy', 'nama_latin' => 'Quraisy', 'jumlah_ayat' => 4, 'jenis' => 'Makkiyah', 'urutan' => 106],
            ['nama' => "Al-Ma'un", 'nama_latin' => "Al-Ma'un", 'jumlah_ayat' => 7, 'jenis' => 'Makkiyah', 'urutan' => 107],
            ['nama' => 'Al-Kausar', 'nama_latin' => 'Al-Kausar', 'jumlah_ayat' => 3, 'jenis' => 'Makkiyah', 'urutan' => 108],
            ['nama' => 'Al-Kafirun', 'nama_latin' => 'Al-Kafirun', 'jumlah_ayat' => 6, 'jenis' => 'Makkiyah', 'urutan' => 109],
            ['nama' => 'An-Nasr', 'nama_latin' => 'An-Nasr', 'jumlah_ayat' => 3, 'jenis' => 'Madaniyah', 'urutan' => 110],
            ['nama' => 'Al-Lahab', 'nama_latin' => 'Al-Lahab', 'jumlah_ayat' => 5, 'jenis' => 'Makkiyah', 'urutan' => 111],
            ['nama' => 'Al-Ikhlas', 'nama_latin' => 'Al-Ikhlas', 'jumlah_ayat' => 4, 'jenis' => 'Makkiyah', 'urutan' => 112],
            ['nama' => 'Al-Falaq', 'nama_latin' => 'Al-Falaq', 'jumlah_ayat' => 5, 'jenis' => 'Makkiyah', 'urutan' => 113],
            ['nama' => 'An-Nas', 'nama_latin' => 'An-Nas', 'jumlah_ayat' => 6, 'jenis' => 'Makkiyah', 'urutan' => 114],
        ];

        // Hapus duplikat berdasarkan urutan
        $unique = [];
        foreach ($surats as $s) {
            $unique[$s['urutan']] = $s;
        }
        ksort($unique);

        DB::table('surats')->insert(array_values($unique));
    }
}
