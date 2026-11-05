<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\PenilaianRaporInternal;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixTahunAjaran20262027 extends Command
{
    protected $signature = 'ta:fix-2026-2027 {--apply : Terapkan perubahan (tanpa flag ini hanya inspeksi / dry-run)}';

    protected $description = 'Perbaikan one-off TA 2026/2027: rename TA aktif yang salah label menjadi 2026/2027, atau koreksi periode TA 2026/2027 yang sudah ada (Ganjil 1 Jul - 31 Des 2026, Genap 1 Jan - 30 Jun 2027)';

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

        $taBaru = TahunAjaran::where('nama', self::TA_BARU)->first();

        return $taBaru
            ? $this->alignTaBaru($taBaru)
            : $this->renameTaLama($semAktif);
    }

    /**
     * Skenario A: TA 2026/2027 belum ada — rename TA aktif yang salah label.
     */
    private function renameTaLama(Semester $semAktif): int
    {
        $taLama = TahunAjaran::where('nama', $semAktif->tahun_ajaran)->first();
        if (! $taLama) {
            $this->error('Record TA "'.$semAktif->tahun_ajaran.'" tidak ditemukan di tabel tahun_ajaran.');

            return self::FAILURE;
        }

        $semesterTerkait = Semester::where('tahun_ajaran', $taLama->nama)->orderBy('tanggal_mulai')->get();
        $taNamaLama = $taLama->nama;

        $this->tampilkanKondisi();

        $this->newLine();
        $this->info('=== RENCANA PERUBAHAN (SKENARIO RENAME) ===');
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

    /**
     * Skenario B: TA 2026/2027 sudah ada (mis. dibuat via UI) — koreksi periodenya
     * ke kanonik: Ganjil 1 Jul - 31 Des 2026 (aktif), Genap 1 Jan - 30 Jun 2027 (nonaktif).
     */
    private function alignTaBaru(TahunAjaran $ta): int
    {
        $ganjilAktifList = Semester::where('tahun_ajaran', self::TA_BARU)
            ->where('jenis', 'ganjil')->where('is_aktif', true)->get();
        if ($ganjilAktifList->count() !== 1) {
            $this->error('Semester Ganjil aktif pada TA '.self::TA_BARU.' berjumlah '.$ganjilAktifList->count().' (harus tepat 1). Perbaiki dulu secara manual.');

            return self::FAILURE;
        }
        $ganjil = $ganjilAktifList->first();
        if ($ganjil->status === 'ditutup') {
            $this->error('Semester Ganjil aktif berstatus "ditutup" — tidak boleh diubah. Perbaiki dulu secara manual.');

            return self::FAILURE;
        }

        $genapList = Semester::where('tahun_ajaran', self::TA_BARU)->where('jenis', 'genap')->get();
        if ($genapList->count() > 1) {
            $this->error('Semester Genap pada TA '.self::TA_BARU.' lebih dari 1. Perbaiki dulu secara manual.');

            return self::FAILURE;
        }
        $genap = $genapList->first();

        $this->tampilkanKondisi();

        // ===== Susun rencana berdasarkan selisih dari kondisi kanonik =====
        $rencana = [];
        if ($ta->tanggal_mulai->format('Y-m-d') !== self::TA_MULAI || $ta->tanggal_selesai->format('Y-m-d') !== self::TA_SELESAI || $ta->status !== 'aktif') {
            $rencana[] = 'TA '.self::TA_BARU.': periode/status menjadi '.self::TA_MULAI.' s/d '.self::TA_SELESAI.' (aktif)';
        }
        if ($ganjil->tanggal_mulai->format('Y-m-d') !== self::GANJIL_MULAI || $ganjil->tanggal_selesai->format('Y-m-d') !== self::GANJIL_SELESAI || $ganjil->status !== 'aktif') {
            $rencana[] = "Semester Ganjil id={$ganjil->id}: periode menjadi ".self::GANJIL_MULAI.' s/d '.self::GANJIL_SELESAI.' (tetap aktif)';
        }
        if ($genap) {
            if ($genap->tanggal_mulai->format('Y-m-d') !== self::GENAP_MULAI || $genap->tanggal_selesai->format('Y-m-d') !== self::GENAP_SELESAI || $genap->is_aktif || $genap->status === 'ditutup') {
                $rencana[] = "Semester Genap id={$genap->id}: periode menjadi ".self::GENAP_MULAI.' s/d '.self::GENAP_SELESAI.' (nonaktif)';
            }
        } else {
            $rencana[] = 'Semester Genap dibuat: '.self::GENAP_MULAI.' s/d '.self::GENAP_SELESAI.' (nonaktif)';
        }
        $perluBackfill = Semester::where('tahun_ajaran', self::TA_BARU)
            ->where(fn ($q) => $q->whereNull('tahun_ajaran_id')->orWhere('tahun_ajaran_id', '!=', $ta->id))
            ->exists();
        if ($perluBackfill) {
            $rencana[] = 'Backfill kolom tahun_ajaran_id.';
        }
        $rencana[] = 'Catat activity log.';

        if (count($rencana) === 1) {
            $this->info('Semua sudah sesuai kondisi kanonik — tidak ada perubahan.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('=== RENCANA PERUBAHAN (SKENARIO KOREKSI PERIODE) ===');
        foreach ($rencana as $i => $r) {
            $this->line(($i + 1).". {$r}");
        }

        if (! $this->option('apply')) {
            $this->newLine();
            $this->warn('DRY-RUN: tidak ada perubahan. Jalankan dengan --apply untuk menerapkan.');

            return self::SUCCESS;
        }

        // ===== Safety: tidak boleh ada data transaksional di luar periode baru =====
        $masalah = $this->cekDataDiLuarPeriode($ganjil, self::GANJIL_MULAI, self::GANJIL_SELESAI);
        if ($genap) {
            $masalah = array_merge($masalah, $this->cekDataDiLuarPeriode($genap, self::GENAP_MULAI, self::GENAP_SELESAI));
        }
        if (! empty($masalah)) {
            $this->error('Dibatalkan — ditemukan data di luar periode baru:');
            foreach ($masalah as $m) {
                $this->error("- {$m}");
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($ta, $ganjil, $genap) {
            $ta->update([
                'tanggal_mulai' => self::TA_MULAI,
                'tanggal_selesai' => self::TA_SELESAI,
                'status' => 'aktif',
            ]);
            $this->info('✔ TA '.self::TA_BARU.' periode '.self::TA_MULAI.' s/d '.self::TA_SELESAI.' (aktif)');

            $ganjil->update([
                'tanggal_mulai' => self::GANJIL_MULAI,
                'tanggal_selesai' => self::GANJIL_SELESAI,
                'is_aktif' => true,
                'status' => 'aktif',
            ]);
            $this->info("✔ Semester Ganjil id={$ganjil->id} periode ".self::GANJIL_MULAI.' s/d '.self::GANJIL_SELESAI.' (AKTIF)');

            if ($genap) {
                $genap->update([
                    'tanggal_mulai' => self::GENAP_MULAI,
                    'tanggal_selesai' => self::GENAP_SELESAI,
                    'is_aktif' => false,
                    'status' => 'nonaktif',
                ]);
                $this->info("✔ Semester Genap id={$genap->id} periode ".self::GENAP_MULAI.' s/d '.self::GENAP_SELESAI.' (nonaktif)');
            } else {
                Semester::create([
                    'tahun_ajaran' => self::TA_BARU,
                    'jenis' => 'genap',
                    'tanggal_mulai' => self::GENAP_MULAI,
                    'tanggal_selesai' => self::GENAP_SELESAI,
                    'is_aktif' => false,
                    'status' => 'nonaktif',
                ]);
                $this->info('✔ Semester Genap dibuat ('.self::GENAP_MULAI.' s/d '.self::GENAP_SELESAI.', nonaktif)');
            }

            DB::table('semesters')->where('tahun_ajaran', self::TA_BARU)->update(['tahun_ajaran_id' => $ta->id]);

            ActivityLog::logCustom(
                'fix_ta',
                'Koreksi periode TA '.self::TA_BARU.' via ta:fix-2026-2027: TA '.self::TA_MULAI.' s/d '.self::TA_SELESAI.'; Ganjil '.self::GANJIL_MULAI.' s/d '.self::GANJIL_SELESAI.' (aktif); Genap '.self::GENAP_MULAI.' s/d '.self::GENAP_SELESAI.' (nonaktif).',
                'App\Models\TahunAjaran',
                $ta->id
            );
        });

        $this->newLine();
        $this->info('=== KONDISI SETELAH PERBAIKAN ===');
        $this->tampilkanKondisi();

        return self::SUCCESS;
    }

    /**
     * Deteksi data transaksional bertanggal di luar rentang [mulai, selesai] untuk sebuah semester.
     *
     * @return array<int, string>
     */
    private function cekDataDiLuarPeriode(Semester $semester, string $mulai, string $selesai): array
    {
        $tabelTanggal = [
            'jurnal_harians' => 'tanggal',
            'jurnal_kelas' => 'tanggal',
            'hafalan_tahfidzs' => 'tanggal_hafalan',
            'kelas_liburs' => 'tanggal',
            'perpindahan_kelas' => 'tanggal',
        ];

        $masalah = [];
        foreach ($tabelTanggal as $tabel => $kolom) {
            if (! Schema::hasTable($tabel) || ! Schema::hasColumn($tabel, 'semester_id') || ! Schema::hasColumn($tabel, $kolom)) {
                continue;
            }
            $diLuar = DB::table($tabel)->where('semester_id', $semester->id)
                ->where(fn ($q) => $q->where($kolom, '<', $mulai)->orWhere($kolom, '>', $selesai));
            $count = $diLuar->count();
            if ($count > 0) {
                $masalah[] = "{$tabel} (semester id={$semester->id}): {$count} baris di luar {$mulai} s/d {$selesai}";
            }
        }

        return $masalah;
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
