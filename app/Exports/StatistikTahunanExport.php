<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Export statistik lengkap ke Excel (multi-sheet, tema gelap futuristik).
 * Memuat perbandingan antar tahun ajaran: maksimal 3 TA ke belakang,
 * minimal 1 TA jika baru ada 1 tahun ajaran.
 */
class StatistikTahunanExport
{
    // Palet futuristik: navy gelap + aksen teal/cyan
    private const DARK = '1A1A2E';

    private const DARK2 = '16213E';

    private const ACCENT = '0C8A5F';

    private const CYAN = '00B4D8';

    private const LIGHT = 'F2F7FB';

    private const BORDER = 'D0D7DE';

    private const GOOD = '0C8A5F';

    private const BAD = 'C62828';

    private const MUTED = '888888';

    public function __construct(
        private array $data,
        private array $taList,
    ) {}

    public function export(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $spreadsheet->removeSheetByIndex(0);

        $this->sheetRingkasan($spreadsheet);
        $this->sheetPerbandingan($spreadsheet);
        $this->sheetPerSemester($spreadsheet);
        $this->sheetMunaqosyah($spreadsheet);
        $this->sheetTahfidz($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    // ═══════════════ HELPERS ═══════════════

    private function col(int $i): string
    {
        return Coordinate::stringFromColumnIndex($i);
    }

    private function judulSheet($sheet, string $judul, string $subJudul, int $jumlahKolom): void
    {
        $last = $this->col($jumlahKolom);
        $sheet->mergeCells("A1:{$last}1");
        $sheet->setCellValue('A1', $judul);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(15)->setColor(new Color('FFFFFF'));
        $sheet->getStyle("A1:{$last}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::DARK);
        $sheet->getStyle('A1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells("A2:{$last}2");
        $sheet->setCellValue('A2', $subJudul);
        $sheet->getStyle('A2')->getFont()->setSize(10)->setColor(new Color('E0E6F0'));
        $sheet->getStyle("A2:{$last}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::DARK2);
        $sheet->getRowDimension(2)->setRowHeight(18);
    }

    private function band($sheet, int $row, string $text, int $jumlahKolom): void
    {
        $last = $this->col($jumlahKolom);
        $sheet->mergeCells("A{$row}:{$last}{$row}");
        $sheet->setCellValue("A{$row}", $text);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FFFFFF'));
        $sheet->getStyle("A{$row}:{$last}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::ACCENT);
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    private function headerRow($sheet, int $row, array $headers, int $startCol = 1): void
    {
        $col = $startCol;
        foreach ($headers as $h) {
            $sheet->setCellValue($this->col($col).$row, $h);
            $col++;
        }
        $range = $this->col($startCol).$row.':'.$this->col($col - 1).$row;
        $sheet->getStyle($range)->getFont()->setBold(true)->setSize(10)->setColor(new Color('FFFFFF'));
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::DARK2);
        $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::BORDER);
        $sheet->getRowDimension($row)->setRowHeight(20);
    }

    private function dataRow($sheet, int $row, array $values, bool $alt = false, int $startCol = 1): void
    {
        $col = $startCol;
        foreach ($values as $v) {
            $sheet->setCellValue($this->col($col).$row, $v);
            $col++;
        }
        $range = $this->col($startCol).$row.':'.$this->col($col - 1).$row;
        if ($alt) {
            $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::LIGHT);
        }
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB(self::BORDER);
    }

    private function fmt(float|int $n): string
    {
        if (floor((float) $n) == (float) $n) {
            return (string) (int) $n;
        }

        return str_replace('.', ',', (string) round((float) $n, 2));
    }

    /**
     * Teks delta vs periode sebelumnya: [teks, warna].
     */
    private function delta(float|int $now, float|int $prev): array
    {
        $diff = $now - $prev;
        if ($diff == 0) {
            return ['= 0', self::MUTED];
        }
        $pctText = '';
        if ($prev != 0) {
            $pct = round(($diff / $prev) * 100, 1);
            $pctText = ' ('.($pct > 0 ? '+' : '').str_replace('.', ',', (string) $pct).'%)';
        }
        if ($diff > 0) {
            return ['▲ +'.$this->fmt($diff).$pctText, self::GOOD];
        }

        return ['▼ '.$this->fmt($diff).$pctText, self::BAD];
    }

    // ═══════════════ SHEET 1: RINGKASAN ═══════════════

    private function sheetRingkasan(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Ringkasan');
        $jumlahKolom = 8;

        $periode = count($this->taList) > 1
            ? reset($this->taList).' — '.end($this->taList)
            : (string) reset($this->taList);
        $this->judulSheet($sheet, 'STATISTIK TARTIL — LAPORAN LENGKAP',
            'Diekspor: '.now()->format('d/m/Y H:i').'  |  Periode: '.$periode, $jumlahKolom);

        // KPI cards (TA terbaru)
        $taTerbaru = end($this->taList);
        $taData = $this->data['perTA'][$taTerbaru] ?? [];
        $tahfidz = $this->data['tahfidz'] ?? [];
        $kpis = [
            ['TOTAL SISWA ('.$taTerbaru.')', $taData['totalSiswa'] ?? 0],
            ['RATA-RATA R2 AKHIR', $taData['rataR2Akhir'] ?? 0],
            ['UJIAN MUNAQOSYAH', $taData['totalMunaqosyah'] ?? 0],
            ['TOTAL JUZ HAFAL', $tahfidz['totalJuzEntries'] ?? 0],
        ];
        $row = 4;
        foreach ($kpis as $i => [$label, $value]) {
            $col = 1 + ($i * 2);
            $c1 = $this->col($col);
            $c2 = $this->col($col + 1);
            $sheet->mergeCells("{$c1}{$row}:{$c2}{$row}");
            $sheet->mergeCells("{$c1}".($row + 1).":{$c2}".($row + 1));
            $sheet->setCellValue("{$c1}{$row}", $label);
            $sheet->setCellValue("{$c1}".($row + 1), $value);
            $sheet->getStyle("{$c1}{$row}")->getFont()->setSize(9)->setColor(new Color('B0B8C8'))->setBold(true);
            $sheet->getStyle("{$c1}".($row + 1))->getFont()->setSize(20)->setBold(true)->setColor(new Color('FFFFFF'));
            $sheet->getStyle("{$c1}{$row}:{$c2}".($row + 1))->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::DARK2);
            $sheet->getStyle("{$c1}{$row}:{$c2}".($row + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        }
        $sheet->getRowDimension(5)->setRowHeight(30);

        // Tabel ringkasan per TA
        $row = 7;
        $this->band($sheet, $row, 'RINGKASAN PER TAHUN AJARAN', $jumlahKolom);
        $row++;
        $this->headerRow($sheet, $row, ['Tahun Ajaran', 'Total Siswa', 'Rata-rata R2 Akhir', 'Rata-rata Hari Mengaji', 'Jml Semester', 'Ujian Munaqosyah', 'Penilaian Rapor', 'Keterangan']);
        $row++;
        foreach ($this->taList as $i => $ta) {
            $d = $this->data['perTA'][$ta] ?? [];
            $isTerbaru = $ta === $taTerbaru;
            $this->dataRow($sheet, $row, [
                $ta,
                $d['totalSiswa'] ?? 0,
                $d['rataR2Akhir'] ?? 0,
                $d['rataMengaji'] ?? 0,
                $d['totalSemester'] ?? 0,
                $d['totalMunaqosyah'] ?? 0,
                $d['totalPenilaian'] ?? 0,
                $isTerbaru ? 'TA TERBARU' : 'Selesai',
            ], $i % 2 === 1);
            if ($isTerbaru) {
                $sheet->getStyle("A{$row}:H{$row}")->getFont()->setBold(true);
                $sheet->getStyle("H{$row}")->getFont()->setColor(new Color(self::ACCENT));
            }
            $row++;
        }

        $widths = [14, 12, 18, 20, 13, 18, 16, 14];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension($this->col($i + 1))->setWidth($w);
        }
        $sheet->freezePane('A9');
    }

    // ═══════════════ SHEET 2: PERBANDINGAN TA ═══════════════

    private function sheetPerbandingan(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Perbandingan TA');

        $nTA = count($this->taList);
        $jumlahKolom = 1 + $nTA + max(0, $nTA - 1);
        $this->judulSheet($sheet, 'PERBANDINGAN ANTAR TAHUN AJARAN',
            'Maksimal 3 tahun ajaran terakhir — kolom Δ menunjukkan perubahan dibanding tahun ajaran sebelumnya', $jumlahKolom);

        // Agregat munaqosyah per TA
        $mqPerTA = [];
        foreach ($this->taList as $ta) {
            $peserta = 0;
            $lulus = 0;
            foreach ($this->data['munaqosyah'][$ta] ?? [] as $tingkat) {
                $peserta += $tingkat['total'];
                $lulus += $tingkat['lulus'];
            }
            $mqPerTA[$ta] = [
                'peserta' => $peserta,
                'lulus' => $lulus,
                'persen' => $peserta > 0 ? round(($lulus / $peserta) * 100, 1) : 0,
            ];
        }

        // Definisi metrik: label => closure per TA
        $metrik = [
            'Total Siswa' => fn ($ta) => $this->data['perTA'][$ta]['totalSiswa'] ?? 0,
            'Rata-rata R2 Akhir' => fn ($ta) => $this->data['perTA'][$ta]['rataR2Akhir'] ?? 0,
            'Rata-rata Hari Mengaji' => fn ($ta) => $this->data['perTA'][$ta]['rataMengaji'] ?? 0,
            'Jumlah Ujian Munaqosyah' => fn ($ta) => $this->data['perTA'][$ta]['totalMunaqosyah'] ?? 0,
            'Peserta Munaqosyah' => fn ($ta) => $mqPerTA[$ta]['peserta'],
            'Kelulusan Munaqosyah (%)' => fn ($ta) => $mqPerTA[$ta]['persen'],
            'Jumlah Penilaian Rapor' => fn ($ta) => $this->data['perTA'][$ta]['totalPenilaian'] ?? 0,
        ];

        // Header: Metrik | per TA | Δ per pasangan TA
        $headers = ['Metrik'];
        foreach ($this->taList as $ta) {
            $headers[] = 'TA '.$ta;
        }
        for ($i = 1; $i < $nTA; $i++) {
            $headers[] = 'Δ '.$this->taList[$i].' vs '.$this->taList[$i - 1];
        }
        $this->headerRow($sheet, 4, $headers);

        $row = 5;
        foreach ($metrik as $label => $fn) {
            $values = array_map($fn, $this->taList);
            $cells = array_merge([$label], $values);
            $this->dataRow($sheet, $row, $cells, ($row - 5) % 2 === 1);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);

            // Kolom delta
            for ($i = 1; $i < $nTA; $i++) {
                [$text, $color] = $this->delta($values[$i], $values[$i - 1]);
                $colDelta = $this->col(1 + $nTA + $i);
                $sheet->setCellValue($colDelta.$row, $text);
                $sheet->getStyle($colDelta.$row)->getFont()->setBold(true)->setColor(new Color($color));
            }
            $row++;
        }

        // Insight otomatis dari pasangan TA terakhir
        if ($nTA >= 2) {
            $row++;
            $this->band($sheet, $row, 'INSIGHT OTOMATIS (TA TERAKHIR vs SEBELUMNYA)', $jumlahKolom);
            $row++;
            $taNow = $this->taList[$nTA - 1];
            $taPrev = $this->taList[$nTA - 2];
            foreach ($metrik as $label => $fn) {
                $now = $fn($taNow);
                $prev = $fn($taPrev);
                $diff = $now - $prev;
                if ($diff == 0) {
                    continue;
                }
                $arah = $diff > 0 ? 'naik' : 'turun';
                [$text] = $this->delta($now, $prev);
                $sheet->setCellValue("A{$row}", "• {$label} {$arah} ".ltrim($text, '▲▼ ')." dibanding TA {$taPrev}.");
                $sheet->mergeCells("A{$row}:".$this->col($jumlahKolom).$row);
                $sheet->getStyle("A{$row}")->getFont()->setSize(10)->setColor(new Color($diff > 0 ? self::GOOD : self::BAD));
                $row++;
            }
        }

        $sheet->getColumnDimension('A')->setWidth(28);
        for ($i = 2; $i <= $jumlahKolom; $i++) {
            $sheet->getColumnDimension($this->col($i))->setWidth($i <= 1 + $nTA ? 14 : 22);
        }
        $sheet->freezePane('B5');
    }

    // ═══════════════ SHEET 3: PER SEMESTER ═══════════════

    private function sheetPerSemester(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Per Semester');
        $jumlahKolom = 6;
        $this->judulSheet($sheet, 'DATA PER SEMESTER',
            'Rincian jumlah siswa, nilai rapor (R2), dan aktivitas jurnal per semester', $jumlahKolom);

        $this->headerRow($sheet, 4, ['Semester', 'Jumlah Siswa', 'Rata R2 Akhir', 'Rata R2 Harian (Info)', 'Rata R2 Penilaian', 'Rata Hari Jurnal']);

        $labels = $this->data['semesterLabels'] ?? [];
        $row = 5;
        foreach ($labels as $i => $label) {
            // Lewati semester dari TA yang tidak masuk daftar perbandingan
            $ta = explode(' ', $label)[0];
            if (! in_array($ta, $this->taList, true)) {
                continue;
            }
            $this->dataRow($sheet, $row, [
                $label,
                $this->data['siswa'][$i] ?? 0,
                $this->data['r2Akhir'][$i] ?? 0,
                $this->data['r2Harian'][$i] ?? 0,
                $this->data['r2Penilaian'][$i] ?? 0,
                $this->data['jurnalHari'][$i] ?? 0,
            ], ($row - 5) % 2 === 1);
            $row++;
        }

        $widths = [22, 13, 13, 18, 17, 15];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension($this->col($i + 1))->setWidth($w);
        }
        $sheet->freezePane('A5');
    }

    // ═══════════════ SHEET 4: MUNAQOSYAH ═══════════════

    private function sheetMunaqosyah(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Munaqosyah');
        $jumlahKolom = 7;
        $this->judulSheet($sheet, 'REKAP MUNAQOSYAH PER TAHUN AJARAN',
            'Ringkasan per tingkat (unit/yayasan/pesantren) beserta detail per ujian', $jumlahKolom);

        $row = 4;
        foreach ($this->taList as $ta) {
            $this->band($sheet, $row, 'TAHUN AJARAN '.$ta, $jumlahKolom);
            $row++;

            $adaData = false;
            foreach ($this->data['munaqosyah'][$ta] ?? [] as $tingkat) {
                if ($tingkat['jumlahUjian'] === 0) {
                    continue;
                }
                $adaData = true;

                // Ringkasan tingkat
                $sheet->mergeCells("A{$row}:G{$row}");
                $sheet->setCellValue("A{$row}",
                    "{$tingkat['label']}: {$tingkat['jumlahUjian']} ujian • {$tingkat['total']} peserta • {$tingkat['lulus']} lulus • {$tingkat['tidakLulus']} tidak lulus • kelulusan {$tingkat['persentaseLulus']}%");
                $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(10)->setColor(new Color(self::DARK));
                $sheet->getStyle("A{$row}:G{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(self::LIGHT);
                $row++;

                $this->headerRow($sheet, $row, ['Nama Ujian', 'Tanggal', 'Status', 'Peserta', 'Lulus', 'Tidak Lulus', '% Lulus']);
                $row++;
                foreach ($tingkat['detailUjian'] as $j => $u) {
                    $this->dataRow($sheet, $row, [
                        $u['nama'], $u['tanggal'], ucfirst($u['status']),
                        $u['total'], $u['lulus'], $u['tidakLulus'], $u['persentaseLulus'].'%',
                    ], $j % 2 === 1);
                    $row++;
                }
                $row++;
            }

            if (! $adaData) {
                $sheet->mergeCells("A{$row}:G{$row}");
                $sheet->setCellValue("A{$row}", 'Belum ada ujian munaqosyah pada tahun ajaran ini.');
                $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(10)->setColor(new Color(self::MUTED));
                $row += 2;
            }
        }

        $widths = [36, 12, 16, 10, 10, 12, 10];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension($this->col($i + 1))->setWidth($w);
        }
    }

    // ═══════════════ SHEET 5: TAHFIDZ ═══════════════

    private function sheetTahfidz(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Tahfidz');
        $jumlahKolom = 4;
        $this->judulSheet($sheet, 'STATISTIK TAHFIDZ & HAFALAN',
            'Kondisi terkini tracking hafalan seluruh kelas tartil aktif', $jumlahKolom);

        $t = $this->data['tahfidz'] ?? [];

        $row = 4;
        $this->band($sheet, $row, 'RINGKASAN', $jumlahKolom);
        $row++;
        $ringkasan = [
            ['Total Siswa (kelas tartil aktif)', $t['totalSiswaTahfidz'] ?? 0],
            ['Total Kelas Tartil Aktif', $t['totalKelasTahfidz'] ?? 0],
            ['Total Setoran Juz Hafal', $t['totalJuzEntries'] ?? 0],
        ];
        foreach ($ringkasan as $i => [$label, $value]) {
            $this->dataRow($sheet, $row, [$label, $value], $i % 2 === 1);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;
        }

        // Distribusi status & kualitas
        $row++;
        $this->band($sheet, $row, 'DISTRIBUSI STATUS SETORAN', $jumlahKolom);
        $row++;
        $this->headerRow($sheet, $row, ['Status', 'Jumlah']);
        $row++;
        foreach ($t['distribusiStatus'] ?? [] as $status => $total) {
            $this->dataRow($sheet, $row, [ucfirst((string) $status), $total], $row % 2 === 0);
            $row++;
        }

        $row++;
        $this->band($sheet, $row, 'DISTRIBUSI KUALITAS SETORAN', $jumlahKolom);
        $row++;
        $this->headerRow($sheet, $row, ['Kualitas', 'Jumlah']);
        $row++;
        foreach ($t['distribusiKualitas'] ?? [] as $kualitas => $total) {
            $this->dataRow($sheet, $row, [ucfirst((string) $kualitas), $total], $row % 2 === 0);
            $row++;
        }

        // Per kelas
        $row++;
        $this->band($sheet, $row, 'PER KELAS TARTIL', $jumlahKolom);
        $row++;
        $this->headerRow($sheet, $row, ['Kelas', 'Guru', 'Jumlah Siswa', 'Rata-rata Juz Hafal/Siswa']);
        $row++;
        foreach ($t['perKelas'] ?? [] as $i => $k) {
            $this->dataRow($sheet, $row, [$k['nama'], $k['guru'], $k['totalSiswa'], $k['avgJuz']], $i % 2 === 1);
            $row++;
        }

        $widths = [30, 24, 14, 22];
        foreach ($widths as $i => $w) {
            $sheet->getColumnDimension($this->col($i + 1))->setWidth($w);
        }
    }
}
