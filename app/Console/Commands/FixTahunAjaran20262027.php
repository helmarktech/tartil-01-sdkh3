<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\PenilaianRaporInternal;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixTahunAjaran20262027 extends Command
{
    protected $signature = 'ta:fix-2026-2027 {--apply : Terapkan perubahan (tanpa flag ini hanya inspeksi / dry-run)}';

    protected $description = 'Perbaikan one-off: rename TA aktif yang salah menjadi 2026/2027, semester aktif menjadi Ganjil (1 Jul - 31 Des 2026), dan buat Genap nonaktif (1 Jan - 30 Jun 2027)';

    private const TA_BARU = '2026/2027';

    private const TA_MULAI = '2026-07-01';

    private const TA_SELESAI = '2027-06-30';

    private const GANJIL_MULAI = '2026-07-01';

    private const GANJIL_SELESAI = '2026-12-31';

    private const GENAP_MULAI = '2027-01-01';

    private const GENAP_SELESAI = '2027-06-30';

    public function handle(): int
    {
        // ===== Guard: tepat satu semester aktif =====
        $semesterAktifList = Semester::where('is_aktif', true)->get();
        if ($semesterAktifList->count() !== 1) {
            $this->error('Ditemukan '.$semesterAktifList->count().' semester aktif (harus tepat 1). Perbaiki dulu secara manual.');

            return self::FAILURE;
        }
        $semAktif = $semesterAktifList->first();

        $taLama = TahunAjaran::where('nama', $semAktif->tahun_ajaran)->first();
        if (! $taLama) {
            $this->error('Record TA "'.$semAktif->tahun_ajaran.'" tidak ditemukan di tabel tahun_ajaran.');

            return self::FAILURE;
        }

        // ===== Idempoten: jika TA 2026/2027 sudah ada, anggap sudah diperbaiki =====
        if (TahunAjaran::where('nama', self::TA_BARU)->exists()) {
            $this->warn('TA '.self::TA_BARU.' sudah ada — perbaikan sepertinya sudah pernah dijalankan. Tidak ada perubahan.');
            $this->tampilkanKondisi();

            return self::SUCCESS;
        }

        $semesterTerkait = Semester::where('tahun_ajaran', $taLama->nama)->orderBy('tanggal_mulai')->get();
        $taNamaLama = $taLama->nama;

        $this->tampilkanKondisi();

        // ===== Rencana perubahan =====
        $this->newLine();
        $this->info('=== RENCANA PERUBAHAN ===');
        $this->line("1. TA [{$taLama->nama}] di-rename menjadi [".self::TA_BARU.'] periode '.self::TA_MULAI.' s/d '.self::TA_SELESAI.' (status tetap aktif).');
        $this->line("2. Semester aktif id={$semAktif->id} [{$semAktif->nama}] menjadi [TA ".self::TA_BARU.' - Ganjil] periode '.self::GANJIL_MULAI.' s/d '.self::GANJIL_SELESAI.', tetap aktif. Semua data yang menempel (siswa, jurnal, hafalan, R2, penilaian) ikut tanpa dipindah.');
        foreach ($semesterTerkait as $s) {
            if ($s->id === $semAktif->id) {
                continue;
            }
            $this->line("3. Semester id={$s->id} [{$s->nama}] ({$s->status}) hanya diubah label TA-nya menjadi ".self::TA_BARU.'; tanggal & status tidak berubah.');
        }
        $this->line('4. Buat semester Genap '.self::TA_BARU.' periode '.self::GENAP_MULAI.' s/d '.self::GENAP_SELESAI.' status nonaktif (dilewati jika sudah ada).');
        $this->line('5. Penilaian rapor pada semester aktif direlabel mengikuti nama baru.');
        $this->line('6. Backfill kolom tahun_ajaran_id + catat activity log.');

        if (! $this->option('apply')) {
            $this->newLine();
            $this->warn('DRY-RUN: tidak ada perubahan. Jalankan dengan --apply untuk menerapkan.');

            return self::SUCCESS;
        }

        // ===== Eksekusi =====
        DB::transaction(function () use ($taLama, $semAktif, $semesterTerkait, $taNamaLama) {
            $taLama->update([
                'nama' => self::TA_BARU,
                'tanggal_mulai' => self::TA_MULAI,
                'tanggal_selesai' => self::TA_SELESAI,
                'status' => 'aktif',
            ]);
            $this->info('✔ TA di-rename menjadi '.self::TA_BARU.' ('.self::TA_MULAI.' s/d '.self::TA_SELESAI.', aktif)');

            $semAktif->update([
                'tahun_ajaran' => self::TA_BARU,
                'jenis' => 'ganjil',
                'tanggal_mulai' => self::GANJIL_MULAI,
                'tanggal_selesai' => self::GANJIL_SELESAI,
                'is_aktif' => true,
                'status' => 'aktif',
            ]);
            $this->info("✔ Semester aktif id={$semAktif->id} menjadi Ganjil ".self::TA_BARU.' ('.self::GANJIL_MULAI.' s/d '.self::GANJIL_SELESAI.', AKTIF)');

            foreach ($semesterTerkait as $s) {
                if ($s->id === $semAktif->id) {
                    continue;
                }
                $s->update(['tahun_ajaran' => self::TA_BARU]);
                $this->info("✔ Semester histori id={$s->id} labelnya mengikuti TA baru (tetap {$s->status})");
            }

            $genapAda = Semester::where('tahun_ajaran', self::TA_BARU)->where('jenis', 'genap')->exists();
            if (! $genapAda) {
                Semester::create([
                    'tahun_ajaran' => self::TA_BARU,
                    'jenis' => 'genap',
                    'tanggal_mulai' => self::GENAP_MULAI,
                    'tanggal_selesai' => self::GENAP_SELESAI,
                    'is_aktif' => false,
                    'status' => 'nonaktif',
                ]);
                $this->info('✔ Semester Genap '.self::TA_BARU.' dibuat ('.self::GENAP_MULAI.' s/d '.self::GENAP_SELESAI.', nonaktif)');
            } else {
                $this->line('- Semester Genap '.self::TA_BARU.' sudah ada, dilewati.');
            }

            // Kolom tahun_ajaran_id tidak ada di $fillable Semester — set via query builder
            DB::table('semesters')->where('tahun_ajaran', self::TA_BARU)->update(['tahun_ajaran_id' => $taLama->id]);

            foreach (PenilaianRaporInternal::where('semester_id', $semAktif->id)->get() as $penilaian) {
                $namaBaru = str_replace($taNamaLama, self::TA_BARU, $penilaian->nama);
                if ($semAktif->jenis === 'ganjil') {
                    $namaBaru = str_replace('Genap', 'Ganjil', $namaBaru);
                }
                if ($namaBaru !== $penilaian->nama) {
                    $penilaian->update(['nama' => $namaBaru]);
                    $this->info("✔ Penilaian id={$penilaian->id} direlabel menjadi \"{$namaBaru}\"");
                }
            }

            ActivityLog::logCustom(
                'fix_ta',
                'Perbaikan TA (edit di tempat) via ta:fix-2026-2027: TA aktif di-rename menjadi '.self::TA_BARU.' ('.self::TA_MULAI.' s/d '.self::TA_SELESAI.'); semester aktif menjadi Ganjil '.self::TA_BARU.' ('.self::GANJIL_MULAI.' s/d '.self::GANJIL_SELESAI.'); Genap '.self::TA_BARU.' nonaktif. Tidak ada data yang dipindah.',
                'App\Models\TahunAjaran',
                $taLama->id
            );
        });

        $this->newLine();
        $this->info('=== KONDISI SETELAH PERBAIKAN ===');
        $this->tampilkanKondisi();

        return self::SUCCESS;
    }

    private function tampilkanKondisi(): void
    {
        $this->info('=== KONDISI TAHUN AJARAN & SEMESTER ===');
        foreach (TahunAjaran::orderBy('nama')->get() as $ta) {
            $this->line("TA {$ta->nama} | {$ta->tanggal_mulai->format('Y-m-d')} s/d {$ta->tanggal_selesai->format('Y-m-d')} | {$ta->status}");
        }
        foreach (Semester::orderBy('tanggal_mulai')->get() as $s) {
            $kelas = DB::table('semester_kelas')->where('semester_id', $s->id)->count();
            $siswa = DB::table('semester_siswa')->where('semester_id', $s->id)->count();
            $aktif = $s->is_aktif ? ' [AKTIF]' : '';
            $this->line("- id={$s->id} {$s->nama} | {$s->tanggal_mulai->format('Y-m-d')} s/d {$s->tanggal_selesai->format('Y-m-d')} | {$s->status}{$aktif} | kelas={$kelas} siswa={$siswa}");
        }
    }
}
