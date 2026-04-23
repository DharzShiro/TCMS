<?php

namespace App\Services\Reports;

use FPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Response;

/**
 * ReportExportService
 *
 * Plan limits enforced:
 *   basic    → no export at all
 *   standard → CSV only, ≤ 3,000 records / month
 *   premium  → CSV + Excel (.xlsx) + PDF, unlimited records
 *
 * Requires:
 *   composer require phpoffice/phpspreadsheet
 *   composer require setasign/fpdf  (already installed)
 */
class ReportExportService
{
    const STANDARD_MONTHLY_LIMIT = 3000;

    // ── TESDA palette (RGB arrays for FPDF) ───────────────────────────────────
    const NAVY       = [0,   48,  135];   // #003087
    const NAVY_DARK  = [26,  58,  107];   // #1a3a6b
    const ROYAL_BLUE = [0,   87,  184];   // #0057B8
    const LIGHT_BLUE = [232, 240, 251];   // #e8f0fb
    const PALE_BLUE  = [240, 245, 255];   // #f0f5ff
    const GOLD       = [201, 168, 76];    // #c9a84c
    const RED        = [206, 17,  38];    // #CE1126
    const WHITE      = [255, 255, 255];
    const MUTED      = [90,  122, 170];   // #5a7aaa

    // ── TESDA palette (hex strings for PhpSpreadsheet, no # prefix) ──────────
    const X_NAVY       = '003087';
    const X_NAVY_DARK  = '1a3a6b';
    const X_ROYAL_BLUE = '0057B8';
    const X_PALE_BLUE  = 'f0f5ff';
    const X_GOLD       = 'c9a84c';
    const X_GOLD_LIGHT = 'fef9ec';
    const X_RED        = 'CE1126';
    const X_WHITE      = 'FFFFFF';
    const X_MUTED      = '5a7aaa';
    const X_BORDER     = 'c5d8f5';

    // ─────────────────────────────────────────────────────────────────────────
    // Public entry point
    // ─────────────────────────────────────────────────────────────────────────

    public function export(
        array  $data,
        string $filename,
        string $format,
        string $title,
        string $plan
    ): Response|\Illuminate\Http\RedirectResponse {

        if ($plan === 'basic') {
            return redirect()->back()
                ->withErrors(['export' => 'Export is not available on the Basic plan. Please upgrade to Standard or Premium.']);
        }

        if ($plan === 'standard' && in_array($format, ['pdf', 'excel'])) {
            return redirect()->back()
                ->withErrors(['export' => ucfirst($format) . ' export is a Premium feature. Please upgrade to access it.']);
        }

        if ($plan === 'standard' && count($data) > self::STANDARD_MONTHLY_LIMIT) {
            $data = array_slice($data, 0, self::STANDARD_MONTHLY_LIMIT);
        }

        return match ($format) {
            'pdf'   => $this->exportPdf($data, $filename, $title),
            'excel' => $this->exportExcel($data, $filename, $title),
            default => $this->exportCsv($data, $filename),
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CSV
    // ─────────────────────────────────────────────────────────────────────────

    protected function exportCsv(array $data, string $filename): Response
    {
        if (empty($data)) {
            $csv = "No data available\n";
        } else {
            $lines   = [];
            $lines[] = implode(',', array_map([$this, 'csvEscape'], array_keys($data[0])));
            foreach ($data as $row) {
                $lines[] = implode(',', array_map([$this, 'csvEscape'], array_values($row)));
            }
            $csv = implode("\n", $lines);
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
        ]);
    }

    protected function csvEscape(mixed $value): string
    {
        $value = (string) $value;
        if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
            $value = '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Excel (.xlsx) — PhpSpreadsheet — TESDA branded
    // ─────────────────────────────────────────────────────────────────────────

    protected function exportExcel(array $data, string $filename, string $title): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report');

        $columns      = empty($data) ? [] : array_keys($data[0]);
        $colCount     = count($columns);
        $lastColLetter = $colCount > 0 ? Coordinate::stringFromColumnIndex($colCount) : 'A';

        // ── Row 1 — Title banner ──────────────────────────────────────────
        $sheet->mergeCells("A1:{$lastColLetter}1");
        $sheet->setCellValue('A1', 'TESDA Training Center Management System');
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
            'font' => [
                'bold'  => true,
                'size'  => 14,
                'color' => ['argb' => 'FF' . self::X_GOLD],
                'name'  => 'Times New Roman',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF' . self::X_NAVY],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // ── Row 2 — Report subtitle ───────────────────────────────────────
        $sheet->mergeCells("A2:{$lastColLetter}2");
        $sheet->setCellValue('A2', $title . '   —   Generated: ' . now()->format('F d, Y  H:i'));
        $sheet->getStyle("A2:{$lastColLetter}2")->applyFromArray([
            'font' => [
                'italic' => true,
                'size'   => 9,
                'color'  => ['argb' => 'FFFFFFFF'],
                'name'   => 'Times New Roman',
            ],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF' . self::X_ROYAL_BLUE],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // ── Row 3 — TESDA tricolor accent bar (Red | Blue | Gold) ─────────
        if ($colCount >= 3) {
            $t1 = (int) floor($colCount / 3);
            $t2 = $t1 * 2;

            $c1e = Coordinate::stringFromColumnIndex($t1);
            $c2s = Coordinate::stringFromColumnIndex($t1 + 1);
            $c2e = Coordinate::stringFromColumnIndex($t2);
            $c3s = Coordinate::stringFromColumnIndex($t2 + 1);

            foreach ([
                ["A3:{$c1e}3",              self::X_RED],
                ["{$c2s}3:{$c2e}3",         self::X_ROYAL_BLUE],
                ["{$c3s}3:{$lastColLetter}3", self::X_GOLD],
            ] as [$range, $color]) {
                $sheet->mergeCells($range);
                $sheet->getStyle($range)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF' . $color);
            }
        } else {
            $sheet->mergeCells("A3:{$lastColLetter}3");
            $sheet->getStyle("A3:{$lastColLetter}3")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF' . self::X_NAVY);
        }
        $sheet->getRowDimension(3)->setRowHeight(5);

        // ── Row 4 — spacer ────────────────────────────────────────────────
        $sheet->mergeCells("A4:{$lastColLetter}4");
        $sheet->getStyle("A4:{$lastColLetter}4")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFf8fbff');
        $sheet->getRowDimension(4)->setRowHeight(8);

        if (empty($data)) {
            $sheet->mergeCells("A5:{$lastColLetter}5");
            $sheet->setCellValue('A5', 'No data available.');
            $sheet->getStyle('A5')->applyFromArray([
                'font'      => ['italic' => true, 'color' => ['argb' => 'FF' . self::X_MUTED]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        } else {
            // ── Row 5 — column headers ────────────────────────────────────
            foreach ($columns as $i => $col) {
                $cell = Coordinate::stringFromColumnIndex($i + 1) . '5';
                $sheet->setCellValue($cell, strtoupper($col));
            }

            $headerRange = "A5:{$lastColLetter}5";
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold'  => true,
                    'size'  => 9,
                    'color' => ['argb' => 'FF' . self::X_WHITE],
                    'name'  => 'Arial',
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF' . self::X_NAVY_DARK],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                    'wrapText'   => true,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color'       => ['argb' => 'FF' . self::X_ROYAL_BLUE],
                    ],
                    'bottom' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color'       => ['argb' => 'FF' . self::X_GOLD],
                    ],
                ],
            ]);
            $sheet->getRowDimension(5)->setRowHeight(22);

            // ── Data rows ─────────────────────────────────────────────────
            foreach ($data as $rowIdx => $rowData) {
                $excelRow = 6 + $rowIdx;
                $isAlt    = ($rowIdx % 2 === 1);
                $bgColor  = $isAlt ? 'FF' . self::X_PALE_BLUE : 'FFFFFFFF';

                foreach (array_values($rowData) as $colIdx => $value) {
                    $cell = Coordinate::stringFromColumnIndex($colIdx + 1) . $excelRow;
                    $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
                }

                $rowRange = "A{$excelRow}:{$lastColLetter}{$excelRow}";
                $sheet->getStyle($rowRange)->applyFromArray([
                    'font' => [
                        'size'  => 9,
                        'color' => ['argb' => 'FF' . self::X_NAVY_DARK],
                        'name'  => 'Arial',
                    ],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => $bgColor],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF' . self::X_BORDER],
                        ],
                    ],
                ]);
                $sheet->getRowDimension($excelRow)->setRowHeight(16);
            }

            // ── Footer row ────────────────────────────────────────────────
            $footerRow = 6 + count($data);
            $sheet->mergeCells("A{$footerRow}:{$lastColLetter}{$footerRow}");
            $sheet->setCellValue(
                "A{$footerRow}",
                'Total Records: ' . number_format(count($data)) . '   |   TCMS Export   |   ' . now()->format('Y-m-d H:i')
            );
            $sheet->getStyle("A{$footerRow}:{$lastColLetter}{$footerRow}")->applyFromArray([
                'font' => [
                    'bold'   => true,
                    'italic' => true,
                    'size'   => 8,
                    'color'  => ['argb' => 'FF' . self::X_GOLD],
                    'name'   => 'Times New Roman',
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF' . self::X_NAVY],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'top' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color'       => ['argb' => 'FF' . self::X_GOLD],
                    ],
                ],
            ]);
            $sheet->getRowDimension($footerRow)->setRowHeight(16);

            // ── Auto-size columns ─────────────────────────────────────────
            foreach (range(1, $colCount) as $colIdx) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIdx))->setAutoSize(true);
            }
        }

        // Freeze panes below header
        $sheet->freezePane('A6');

        // Document metadata
        $spreadsheet->getProperties()
            ->setCreator('TCMS')
            ->setTitle($title)
            ->setSubject('TESDA Training Center Report')
            ->setDescription('Generated by TCMS on ' . now()->format('Y-m-d'));

        // Output
        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PDF (FPDF) — full TESDA branding
    // ─────────────────────────────────────────────────────────────────────────

    protected function exportPdf(array $data, string $filename, string $title): Response
    {
        $pdf = new class('L', 'mm', 'A4') extends FPDF {
            // Circle / Ellipse support
            public function Ellipse(float $x, float $y, float $rx, float $ry, string $style = 'D'): void
            {
                $op = match ($style) { 'F' => 'f', 'FD', 'DF' => 'B', default => 'S' };
                $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
                $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;
                $k  = $this->k;
                $h  = $this->h;
                $this->_out(sprintf(
                    '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c '
                    . '%.2F %.2F %.2F %.2F %.2F %.2F c '
                    . '%.2F %.2F %.2F %.2F %.2F %.2F c '
                    . '%.2F %.2F %.2F %.2F %.2F %.2F c %s',
                    ($x+$rx)*$k,  ($h-$y)*$k,
                    ($x+$rx)*$k,  ($h-($y-$ly))*$k, ($x+$lx)*$k,  ($h-($y-$ry))*$k, $x*$k, ($h-($y-$ry))*$k,
                    ($x-$lx)*$k,  ($h-($y-$ry))*$k, ($x-$rx)*$k,  ($h-($y-$ly))*$k, ($x-$rx)*$k, ($h-$y)*$k,
                    ($x-$rx)*$k,  ($h-($y+$ly))*$k, ($x-$lx)*$k,  ($h-($y+$ry))*$k, $x*$k, ($h-($y+$ry))*$k,
                    ($x+$lx)*$k,  ($h-($y+$ry))*$k, ($x+$rx)*$k,  ($h-($y+$ly))*$k, ($x+$rx)*$k, ($h-$y)*$k,
                    $op
                ));
            }
            public function Circle(float $x, float $y, float $r, string $style = 'D'): void
            {
                $this->Ellipse($x, $y, $r, $r, $style);
            }
        };

        $pdf->SetAutoPageBreak(true, 18);
        $pdf->SetMargins(12, 12, 12);
        $pdf->AddPage();

        $W      = 273; // usable width
        $startX = 12;

        // ── Page background ───────────────────────────────────────────────
        $pdf->SetFillColor(254, 252, 245);
        $pdf->Rect(0, 0, 297, 210, 'F');

        // Subtle diagonal watermark lines
        $pdf->SetDrawColor(240, 235, 220);
        $pdf->SetLineWidth(0.07);
        for ($x = -210; $x < 297 + 210; $x += 10) {
            $pdf->Line($x, 0, $x + 210, 210);
        }
        $pdf->SetLineWidth(0.2);

        // ── Outer navy border frame ───────────────────────────────────────
        $pdf->SetDrawColor(...self::NAVY);
        $pdf->SetLineWidth(1.0);
        $pdf->Rect(5, 5, 287, 200);

        $pdf->SetDrawColor(...self::GOLD);
        $pdf->SetLineWidth(0.35);
        $pdf->Rect(7, 7, 283, 196);

        // ── Header band ───────────────────────────────────────────────────
        $pdf->SetFillColor(...self::NAVY);
        $pdf->Rect($startX, 10, $W, 30, 'F');

        // Left red sidebar accent
        $pdf->SetFillColor(...self::RED);
        $pdf->Rect($startX, 10, 7, 30, 'F');

        // Right gold sidebar accent
        $pdf->SetFillColor(...self::GOLD);
        $pdf->Rect($startX + $W - 7, 10, 7, 30, 'F');

        // Left app logo
        $logoPath = public_path('assets/app_logo.png');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, $startX + 9, 14, 18, 18);
        }

        // Right app logo (mirrored position)
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, $startX + $W - 27, 14, 18, 18);
        }

        // Republic of Philippines
        $pdf->SetTextColor(...self::GOLD);
        $pdf->SetFont('Times', 'I', 7.5);
        $pdf->SetXY($startX, 13);
        $pdf->Cell($W, 0, 'Republic of the Philippines', 0, 0, 'C');

        // Main org name
        $pdf->SetTextColor(...self::WHITE);
        $pdf->SetFont('Times', 'B', 11.5);
        $pdf->SetXY($startX, 19);
        $pdf->Cell($W, 0, 'Technical Education and Skills Development Authority', 0, 0, 'C');

        // Tagline
        $pdf->SetTextColor(...self::GOLD);
        $pdf->SetFont('Times', 'I', 7);
        $pdf->SetXY($startX, 27);
        $pdf->Cell($W, 0, '"Empowering the Filipino Workforce"', 0, 0, 'C');

        // ── Tricolor bar ──────────────────────────────────────────────────
        $barY   = 41;
        $third  = $W / 3;

        $pdf->SetFillColor(...self::RED);
        $pdf->Rect($startX, $barY, $third, 3.5, 'F');
        $pdf->SetFillColor(...self::ROYAL_BLUE);
        $pdf->Rect($startX + $third, $barY, $third, 3.5, 'F');
        $pdf->SetFillColor(...self::GOLD);
        $pdf->Rect($startX + $third * 2, $barY, $third, 3.5, 'F');

        // ── Report title ──────────────────────────────────────────────────
        $pdf->SetTextColor(...self::NAVY);
        $pdf->SetFont('Times', 'B', 18);
        $pdf->SetXY($startX, 48);
        $pdf->Cell($W, 0, $title, 0, 0, 'C');

        // Generated date
        $pdf->SetTextColor(...self::MUTED);
        $pdf->SetFont('Times', 'I', 8);
        $pdf->SetXY($startX, 57);
        $pdf->Cell($W, 0, 'Generated: ' . now()->format('F d, Y  H:i:s'), 0, 0, 'C');

        // Gold ornament divider
        $cx   = $startX + $W / 2;
        $half = 55;
        $pdf->SetDrawColor(...self::GOLD);
        $pdf->SetLineWidth(0.35);
        $pdf->Line($cx - $half, 63, $cx + $half, 63);
        $pdf->SetFillColor(...self::GOLD);
        $pdf->SetLineWidth(0.1);
        foreach ([$cx, $cx - $half, $cx + $half] as $dotX) {
            $pdf->Circle($dotX, 63, 0.8, 'F');
        }

        $pdf->SetY(68);

        // ── Table ─────────────────────────────────────────────────────────
        if (empty($data)) {
            $pdf->SetTextColor(...self::MUTED);
            $pdf->SetFont('Times', 'I', 11);
            $pdf->SetX($startX);
            $pdf->Cell($W, 10, 'No data available.', 0, 1, 'C');
        } else {
            $columns  = array_keys($data[0]);
            $colCount = count($columns);
            $colW     = $W / $colCount;

            // Column headers
            $pdf->SetFillColor(...self::NAVY);
            $pdf->SetTextColor(...self::WHITE);
            $pdf->SetDrawColor(180, 200, 235);
            $pdf->SetFont('Times', 'B', 8);
            $pdf->SetLineWidth(0.25);
            $pdf->SetX($startX);

            foreach ($columns as $col) {
                $pdf->Cell($colW, 9, $this->truncate(strtoupper($col), 20), 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Bottom gold line under headers
            $pdf->SetDrawColor(...self::GOLD);
            $pdf->SetLineWidth(0.5);
            $pdf->Line($startX, $pdf->GetY(), $startX + $W, $pdf->GetY());
            $pdf->SetLineWidth(0.2);

            // Data rows
            $pdf->SetFont('Times', '', 7.5);
            $pdf->SetLineWidth(0.18);

            foreach ($data as $i => $row) {
                // Page break — repeat header
                if ($pdf->GetY() > 172) {
                    $pdf->AddPage();

                    // Minimal page continuation header
                    $pdf->SetFillColor(...self::NAVY);
                    $pdf->Rect($startX, 10, $W, 10, 'F');
                    $pdf->SetTextColor(...self::GOLD);
                    $pdf->SetFont('Times', 'B', 8);
                    $pdf->SetXY($startX, 12);
                    $pdf->Cell($W, 0, $title . '  (continued)', 0, 0, 'C');

                    $pdf->SetFillColor(...self::RED);
                    $pdf->Rect($startX, 21, $W / 3, 2, 'F');
                    $pdf->SetFillColor(...self::ROYAL_BLUE);
                    $pdf->Rect($startX + $W / 3, 21, $W / 3, 2, 'F');
                    $pdf->SetFillColor(...self::GOLD);
                    $pdf->Rect($startX + $W * 2 / 3, 21, $W / 3, 2, 'F');

                    $pdf->SetY(25);

                    // Repeat column headers
                    $pdf->SetFillColor(...self::NAVY);
                    $pdf->SetTextColor(...self::WHITE);
                    $pdf->SetDrawColor(180, 200, 235);
                    $pdf->SetFont('Times', 'B', 8);
                    $pdf->SetLineWidth(0.25);
                    $pdf->SetX($startX);
                    foreach ($columns as $col) {
                        $pdf->Cell($colW, 9, $this->truncate(strtoupper($col), 20), 1, 0, 'C', true);
                    }
                    $pdf->Ln();

                    $pdf->SetFont('Times', '', 7.5);
                    $pdf->SetLineWidth(0.18);
                }

                $isAlt = ($i % 2 === 1);
                $pdf->SetFillColor(...($isAlt ? self::PALE_BLUE : self::WHITE));
                $pdf->SetTextColor(...self::NAVY_DARK);
                $pdf->SetDrawColor(210, 225, 245);
                $pdf->SetX($startX);

                foreach (array_values($row) as $cell) {
                    $pdf->Cell($colW, 7, $this->truncate((string) $cell, 28), 1, 0, 'L', true);
                }
                $pdf->Ln();
            }

            // Footer row — total records
            $pdf->SetFillColor(...self::NAVY);
            $pdf->SetTextColor(...self::GOLD);
            $pdf->SetDrawColor(...self::NAVY);
            $pdf->SetFont('Times', 'B', 8);
            $pdf->SetLineWidth(0.4);
            $pdf->SetX($startX);
            $pdf->Cell($colW * $colCount, 8, 'Total Records: ' . number_format(count($data)), 1, 1, 'R', true);
        }

        // ── Page footer ───────────────────────────────────────────────────
        $pdf->SetY(-13);
        $pdf->SetDrawColor(...self::GOLD);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($startX, $pdf->GetY(), $startX + $W, $pdf->GetY());

        $pdf->SetY($pdf->GetY() + 1);
        $pdf->SetTextColor(...self::MUTED);
        $pdf->SetFont('Times', 'I', 6.5);
        $pdf->SetX($startX);
        $pdf->Cell($W / 2, 5, 'TCMS Export  |  Confidential', 0, 0, 'L');
        $pdf->Cell($W / 2, 5, 'Page ' . $pdf->PageNo() . '  |  ' . now()->format('Y-m-d H:i'), 0, 0, 'R');

        $output = $pdf->Output('S');

        return response($output, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.pdf"',
        ]);
    }

    private function truncate(string $value, int $max): string
    {
        return mb_strlen($value) > $max ? mb_substr($value, 0, $max - 1) . '…' : $value;
    }
}