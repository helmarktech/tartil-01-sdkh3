<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TahunAjaran;
use App\Models\Semester;
use Carbon\Carbon;

class TahunAjaranSemesterSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== TahunAjaranSemesterSeeder ===');

        // ════════════════════════════════════════════
        // STEP 1: Buat / Pastikan Tahun Ajaran ada
        // ════════════════════════════════════════════
        $taNama = '2025/2026';
        $ta = TahunAjaran::where('nama', $taNama)->first();

        if (!$ta) {
            $ta = TahunAjaran::create([
                'nama' => $taNama,
                'tanggal_mulai' => '2025-07-01',
                'tanggal_selesai' => '2026-12-20',
                'status' => 'aktif',
            ]);
            $this->command->info("  TahunAjaran '{$taNama}' CREATED (id={$ta->id})");
        } else {
            $ta->update(['status' => 'aktif', 'tanggal_selesai' => '2026-12-20']);
            $this->command->info("  TahunAjaran '{$taNama}' EXISTS (id={$ta->id}) — updated to aktif");
        }

        // Double-check
        if (!$ta || !$ta->exists) {
            $this->command->error("  FAILED: TahunAjaran '{$taNama}' could not be created!");
            return;
        }

        // ════════════════════════════════════════════
        // STEP 2: Semester Ganjil — SUDAH DITUTUP
        // ════════════════════════════════════════════
        $ganjil = Semester::where('tahun_ajaran', $taNama)->where('jenis', 'ganjil')->first();

        if (!$ganjil) {
            $ganjil = Semester::create([
                'tahun_ajaran' => $taNama,
                'jenis' => 'ganjil',
                'tanggal_mulai' => '2025-07-01',
                'tanggal_selesai' => '2025-12-20',
                'is_aktif' => false,
                'status' => 'ditutup',
            ]);
            $this->command->info("  Semester Ganjil CREATED (id={$ganjil->id})");
        } else {
            $ganjil->update(['status' => 'ditutup', 'is_aktif' => false]);
            $this->command->info("  Semester Ganjil EXISTS (id={$ganjil->id}) — updated to ditutup");
        }

        // ════════════════════════════════════════════
        // STEP 3: Semester Genap — AKTIF
        // Tanggal diperpanjang agar mencakup periode saat ini
        // untuk keperluan uji coba (now() = Juli 2026)
        // ════════════════════════════════════════════
        $genap = Semester::where('tahun_ajaran', $taNama)->where('jenis', 'genap')->first();

        $genapMulai = '2026-01-05';
        $genapSelesai = '2026-12-20'; // Diperpanjang agar semester masih aktif saat uji coba

        if (!$genap) {
            $genap = Semester::create([
                'tahun_ajaran' => $taNama,
                'jenis' => 'genap',
                'tanggal_mulai' => $genapMulai,
                'tanggal_selesai' => $genapSelesai,
                'is_aktif' => true,
                'status' => 'aktif',
            ]);
            $this->command->info("  Semester Genap CREATED (id={$genap->id}) | {$genapMulai} s/d {$genapSelesai}");
        } else {
            $genap->update([
                'status' => 'aktif',
                'is_aktif' => true,
                'tanggal_mulai' => $genapMulai,
                'tanggal_selesai' => $genapSelesai,
            ]);
            $this->command->info("  Semester Genap EXISTS (id={$genap->id}) — updated | {$genapMulai} s/d {$genapSelesai}");
        }

        // ════════════════════════════════════════════
        // SUMMARY
        // ════════════════════════════════════════════
        $taCount = TahunAjaran::count();
        $semCount = Semester::count();
        $this->command->info("  Summary: TA={$taCount}, Semesters={$semCount}");
        $this->command->info("  Ganjil: {$ganjil->status} | Genap: {$genap->status}");
    }
}
