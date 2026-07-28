<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class RekapNilaiExport
{
    protected $siswaList;
    protected $tanggalList;
    protected $rekapData;
    protected $summaryPerTanggal;
    protected $kelasNama;
    protected $bulanNama;

    public function __construct($siswaList, $tanggalList, $rekapData, $summaryPerTanggal, $kelasNama, $bulanNama)
    {
        $this->siswaList = $siswaList;
        $this->tanggalList = $tanggalList;
        $this->rekapData = $rekapData;
        $this->summaryPerTanggal = $summaryPerTanggal;
        $this->kelasNama = $kelasNama;
        $this->bulanNama = $bulanNama;
    }

    private function setCell($sheet, int $col, int $row, $value): void
    {
        $coord = Coordinate::stringFromColumnIndex($col) . $row;
        $sheet->setCellValue($coord, $value);
    }

    private function cellCoord(int $col, int $row): string
    {
        return Coordinate::stringFromColumnIndex($col) . $row;
    }

    public function export(): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Nilai');

        // Colors
        $colorHeaderBg = 'F5F0EB';
        $colorSummaryBg = 'EBE5DB';
        $colorPctBg = 'D9D0C5';
        $colorBorder = 'C4B8A8';
        $colorGreen = 'E9F0E9';
        $colorYellow = 'FFF8E1';
        $colorRed = 'FBE9E7';
        $colorB = '5A7D5A';
        $colorC = 'B8860B';
        $colorK = 'C62828';
        $colorHeader = '8B7355';

        $totalTanggal = count($this->tanggalList);
        $lastDataCol = 4 + $totalTanggal + 3; // NO + NIS + KELAS + NAMA + tanggal + B+C + K + %
        $lastColLetter = Coordinate::stringFromColumnIndex($lastDataCol);

        // ===== TITLE =====
        $sheet->setCellValue('A1', 'REKAP NILAI');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18)->setColor(new Color($colorHeader));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('A2', $this->kelasNama . ' — ' . $this->bulanNama);
        $sheet->mergeCells('A2:D2');
        $sheet->getStyle('A2')->getFont()->setSize(11)->setColor(new Color('8A8A8A'));

        // ===== SUMMARY CARDS =====
        $sheet->setCellValue('A4', 'SISWA');
        $sheet->setCellValue('A5', $this->siswaList->count());
        $sheet->setCellValue('C4', 'PERTEMUAN');
        $sheet->setCellValue('C5', $totalTanggal);
        $sheet->setCellValue('E4', 'RATA-RATA');
        $sheet->setCellValue('E5', $this->hitungRataRataKelas() . '%');

        foreach (['A4', 'C4', 'E4'] as $cell) {
            $sheet->getStyle($cell)->getFont()->setSize(10)->setColor(new Color('8A8A8A'));
        }
        foreach (['A5', 'C5', 'E5'] as $cell) {
            $sheet->getStyle($cell)->getFont()->setBold(true)->setSize(22);
        }

        // ===== HEADERS =====
        $headerRow = 7;
        $col = 1;

        $this->setCell($sheet, $col++, $headerRow, 'NO');
        $this->setCell($sheet, $col++, $headerRow, 'NIS');
        $this->setCell($sheet, $col++, $headerRow, 'KELAS');
        $this->setCell($sheet, $col++, $headerRow, 'NAMA SISWA');

        $colTanggalStart = $col;
        foreach ($this->tanggalList as $t) {
            $this->setCell($sheet, $col, $headerRow, $t['tanggal']->format('d'));
            $this->setCell($sheet, $col, $headerRow + 1, strtoupper($t['tanggal']->format('M')));
            $col++;
        }

        $this->setCell($sheet, $col++, $headerRow, 'B+C');
        $this->setCell($sheet, $col++, $headerRow, 'K');
        $this->setCell($sheet, $col, $headerRow, '%');

        // Merge header cells
        $sheet->mergeCells('A7:A8');
        $sheet->mergeCells('B7:B8');
        $sheet->mergeCells('C7:C8');
        $sheet->mergeCells('D7:D8');

        $lastCol = $col;
        $headerRange = 'A7:' . $this->cellCoord($lastCol, $headerRow + 1);
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorHeaderBg);
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10)->setColor(new Color($colorHeader));
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($headerRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($colorBorder);

        // ===== DATA SISWA =====
        $row = $headerRow + 2;
        foreach ($this->siswaList as $i => $s) {
            $dataSiswa = $this->rekapData[$s->id] ?? [];
            $countBaikCukup = $dataSiswa['summary']['b_c'] ?? 0;
            $countKurang = $dataSiswa['summary']['k'] ?? 0;
            $totalPertemuan = count($this->tanggalList);
            $persen = $totalPertemuan > 0 ? round(($countBaikCukup / $totalPertemuan) * 100) : 0;

            $col = 1;
            $this->setCell($sheet, $col++, $row, $i + 1);
            $this->setCell($sheet, $col++, $row, $s->nis ?? '-');
            $this->setCell($sheet, $col++, $row, $s->kelasReguler->nama ?? '-');
            $this->setCell($sheet, $col++, $row, $s->nama);

            foreach ($this->tanggalList as $t) {
                $nilai = $dataSiswa['nilai'][$t['tanggal_str']] ?? null;
                $this->setCell($sheet, $col, $row, $nilai ?? '-');
                $cellCoord = $this->cellCoord($col, $row);
                $sheet->getStyle($cellCoord)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                if ($nilai == 'B') {
                    $sheet->getStyle($cellCoord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorGreen);
                    $sheet->getStyle($cellCoord)->getFont()->setColor(new Color($colorB))->setBold(true);
                } elseif ($nilai == 'C') {
                    $sheet->getStyle($cellCoord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorYellow);
                    $sheet->getStyle($cellCoord)->getFont()->setColor(new Color($colorC))->setBold(true);
                } elseif ($nilai == 'K') {
                    $sheet->getStyle($cellCoord)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorRed);
                    $sheet->getStyle($cellCoord)->getFont()->setColor(new Color($colorK))->setBold(true);
                }
                $col++;
            }

            $this->setCell($sheet, $col, $row, $countBaikCukup);
            $sheet->getStyle($this->cellCoord($col, $row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;

            $this->setCell($sheet, $col, $row, $countKurang);
            $sheet->getStyle($this->cellCoord($col, $row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($this->cellCoord($col, $row))->getFont()->setColor(new Color($colorK));
            $col++;

            $this->setCell($sheet, $col, $row, $persen . '%');
            $sheet->getStyle($this->cellCoord($col, $row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($this->cellCoord($col, $row))->getFont()->setBold(true);

            // Borders data row
            $dataRange = 'A' . $row . ':' . $this->cellCoord($col, $row);
            $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($colorBorder);

            $row++;
        }

        // ===== SUMMARY ROWS =====
        // Separator
        $sepRange = 'A' . $row . ':' . $this->cellCoord($lastCol, $row);
        $sheet->getStyle($sepRange)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->getColor()->setRGB($colorBorder);

        // B+C row
        $col = 1;
        $this->setCell($sheet, $col + 3, $row, 'B+C');
        $sheet->getStyle($this->cellCoord($col + 3, $row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($this->cellCoord($col + 3, $row))->getFont()->setBold(true);
        $col = 5;
        foreach ($this->tanggalList as $t) {
            $val = $this->summaryPerTanggal[$t['tanggal_str']]['b_c'] ?? 0;
            $this->setCell($sheet, $col, $row, $val);
            $sheet->getStyle($this->cellCoord($col, $row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        $sumRange = 'A' . $row . ':' . $this->cellCoord($lastCol, $row);
        $sheet->getStyle($sumRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorSummaryBg);
        $sheet->getStyle($sumRange)->getFont()->setBold(true);
        $sheet->getStyle($sumRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($colorBorder);
        $row++;

        // K row
        $col = 1;
        $this->setCell($sheet, $col + 3, $row, 'K');
        $sheet->getStyle($this->cellCoord($col + 3, $row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($this->cellCoord($col + 3, $row))->getFont()->setBold(true)->setColor(new Color($colorK));
        $col = 5;
        foreach ($this->tanggalList as $t) {
            $val = $this->summaryPerTanggal[$t['tanggal_str']]['k'] ?? 0;
            $this->setCell($sheet, $col, $row, $val);
            $sheet->getStyle($this->cellCoord($col, $row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        $sumRange = 'A' . $row . ':' . $this->cellCoord($lastCol, $row);
        $sheet->getStyle($sumRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorSummaryBg);
        $sheet->getStyle($sumRange)->getFont()->setBold(true);
        $sheet->getStyle($sumRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($colorBorder);
        $row++;

        // PROSENTASE HASIL (%) row
        $col = 1;
        $this->setCell($sheet, $col + 3, $row, 'PROSENTASE HASIL (%)');
        $sheet->getStyle($this->cellCoord($col + 3, $row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle($this->cellCoord($col + 3, $row))->getFont()->setBold(true)->setSize(9);
        $col = 5;
        $totalSiswa = $this->siswaList->count();
        foreach ($this->tanggalList as $t) {
            $bc = $this->summaryPerTanggal[$t['tanggal_str']]['b_c'] ?? 0;
            $pct = $totalSiswa > 0 ? round(($bc / $totalSiswa) * 100) : 0;
            $this->setCell($sheet, $col, $row, $pct . '%');
            $sheet->getStyle($this->cellCoord($col, $row))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $col++;
        }
        $pctRange = 'A' . $row . ':' . $this->cellCoord($lastCol, $row);
        $sheet->getStyle($pctRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorPctBg);
        $sheet->getStyle($pctRange)->getFont()->setBold(true);
        $sheet->getStyle($pctRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB($colorBorder);
        $sheet->getStyle($pctRange)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);

        // ===== COLUMN WIDTHS =====
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(10);
        $sheet->getColumnDimension('D')->setWidth(25);
        $col = 5;
        foreach ($this->tanggalList as $t) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setWidth(6);
            $col++;
        }
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setWidth(7);
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col + 1))->setWidth(7);
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col + 2))->setWidth(8);

        // Freeze panes
        $sheet->freezePane('E9');

        return $spreadsheet;
    }

    private function hitungRataRataKelas(): int
    {
        $totalPersen = 0;
        $siswaCount = $this->siswaList->count();
        $tglCount = count($this->tanggalList);
        if ($siswaCount === 0 || $tglCount === 0) return 0;

        foreach ($this->rekapData as $data) {
            $totalPersen += round(($data['summary']['b_c'] / $tglCount) * 100);
        }
        return round($totalPersen / $siswaCount);
    }
}
