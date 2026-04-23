<?php

namespace App\Services\Pdf;

use FPDF;

/**
 * FPDF does not have a Circle() method natively.
 * We extend it here to add Circle() + Ellipse() support.
 */
class FpdfWithCircle extends FPDF
{
    public function Ellipse(float $x, float $y, float $rx, float $ry, string $style = 'D'): void
    {
        if ($style === 'F') {
            $op = 'f';
        } elseif ($style === 'FD' || $style === 'DF') {
            $op = 'B';
        } else {
            $op = 'S';
        }

        $lx = 4 / 3 * (M_SQRT2 - 1) * $rx;
        $ly = 4 / 3 * (M_SQRT2 - 1) * $ry;
        $k  = $this->k;
        $h  = $this->h;

        $this->_out(sprintf(
            '%.2F %.2F m %.2F %.2F %.2F %.2F %.2F %.2F c '
            . '%.2F %.2F %.2F %.2F %.2F %.2F c '
            . '%.2F %.2F %.2F %.2F %.2F %.2F c '
            . '%.2F %.2F %.2F %.2F %.2F %.2F c %s',
            ($x + $rx) * $k,        ($h - $y) * $k,
            ($x + $rx) * $k,        ($h - ($y - $ly)) * $k,
            ($x + $lx) * $k,        ($h - ($y - $ry)) * $k,
            $x * $k,                 ($h - ($y - $ry)) * $k,
            ($x - $lx) * $k,        ($h - ($y - $ry)) * $k,
            ($x - $rx) * $k,        ($h - ($y - $ly)) * $k,
            ($x - $rx) * $k,        ($h - $y) * $k,
            ($x - $rx) * $k,        ($h - ($y + $ly)) * $k,
            ($x - $lx) * $k,        ($h - ($y + $ry)) * $k,
            $x * $k,                 ($h - ($y + $ry)) * $k,
            ($x + $lx) * $k,        ($h - ($y + $ry)) * $k,
            ($x + $rx) * $k,        ($h - ($y + $ly)) * $k,
            ($x + $rx) * $k,        ($h - $y) * $k,
            $op
        ));
    }

    public function Circle(float $x, float $y, float $r, string $style = 'D'): void
    {
        $this->Ellipse($x, $y, $r, $r, $style);
    }
}


/**
 * TESDA Certificate PDF Generator
 *
 * Usage:
 *   $generator = new TesdaCertificatePdf($certificate);
 *   return $generator->stream();   // preview in browser
 *   return $generator->download(); // force download
 *   $generator->save('/path/to/file.pdf');
 */
class TesdaCertificatePdf
{
    /** @var FpdfWithCircle */
    protected $pdf;

    protected $certificate;

    // Page dimensions (A4 Landscape in mm)
    const W = 297;
    const H = 210;

    // Color palette
    const NAVY  = [26,  58,  107];
    const GOLD  = [201, 168, 76];
    const WHITE = [255, 255, 255];
    const LGRAY = [240, 240, 240];
    const DGRAY = [100, 100, 100];

    public function __construct($certificate)
    {
        $this->certificate = $certificate;
        $this->pdf = new FpdfWithCircle('L', 'mm', 'A4');
        $this->pdf->SetMargins(0, 0, 0);
        $this->pdf->SetAutoPageBreak(false);
        $this->pdf->AddPage();
        $this->build();
    }

    // -------------------------------------------------------------------------
    // Public output methods
    // -------------------------------------------------------------------------

    public function stream(string $filename = 'certificate.pdf'): \Illuminate\Http\Response
    {
        $output = $this->pdf->Output('S');
        return response($output, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function download(string $filename = 'certificate.pdf'): \Illuminate\Http\Response
    {
        $output = $this->pdf->Output('S');
        return response($output, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function save(string $path): void
    {
        $this->pdf->Output('F', $path);
    }

    // -------------------------------------------------------------------------
    // Build certificate
    // -------------------------------------------------------------------------

    protected function build(): void
    {
        $this->drawBackground();
        $this->drawBorders();
        $this->drawCornerOrnaments();
        $this->drawHeader();
        $this->drawTesdaBadge();
        $this->drawCertificateTitle();
        $this->drawDivider(105);
        $this->drawBody();
        $this->drawDivider(170);
        $this->drawDetailsRow();
        $this->drawSignatures();
        $this->drawCertNumber();
    }

    // -------------------------------------------------------------------------
    // Drawing helpers
    // -------------------------------------------------------------------------

    protected function setColor(string $type, array $rgb): void
    {
        [$r, $g, $b] = $rgb;
        match($type) {
            'fill' => $this->pdf->SetFillColor($r, $g, $b),
            'draw' => $this->pdf->SetDrawColor($r, $g, $b),
            'text' => $this->pdf->SetTextColor($r, $g, $b),
        };
    }

    protected function drawBackground(): void
    {
        $this->setColor('fill', [254, 252, 245]);
        $this->pdf->Rect(0, 0, self::W, self::H, 'F');

        $this->setColor('draw', [240, 230, 210]);
        $this->pdf->SetLineWidth(0.1);
        for ($x = -self::H; $x < self::W + self::H; $x += 12) {
            $this->pdf->Line($x, 0, $x + self::H, self::H);
        }
        $this->pdf->SetLineWidth(0.2);
    }

    protected function drawBorders(): void
    {
        $this->setColor('draw', self::NAVY);
        $this->pdf->SetLineWidth(1.2);
        $this->pdf->Rect(5, 5, self::W - 10, self::H - 10);

        $this->setColor('draw', self::GOLD);
        $this->pdf->SetLineWidth(0.4);
        $this->pdf->Rect(8, 8, self::W - 16, self::H - 16);
    }

    protected function drawCornerOrnaments(): void
    {
        $this->setColor('draw', self::GOLD);
        $this->pdf->SetLineWidth(0.8);
        $size = 10;
        $corners = [
            [5,            5,            1,  1 ],
            [self::W - 5,  5,           -1,  1 ],
            [5,            self::H - 5,  1, -1 ],
            [self::W - 5,  self::H - 5, -1, -1 ],
        ];

        foreach ($corners as [$x, $y, $dx, $dy]) {
            $this->pdf->Line($x, $y, $x + $dx * $size, $y);
            $this->pdf->Line($x, $y, $x, $y + $dy * $size);
        }

        $this->setColor('fill', self::GOLD);
        $this->pdf->SetLineWidth(0.1);
        foreach ($corners as [$x, $y, $dx, $dy]) {
            $this->pdf->Circle($x + $dx * 0.5, $y + $dy * 0.5, 0.8, 'F');
        }
    }

    protected function drawHeader(): void
    {
        $this->setColor('fill', self::NAVY);
        $this->pdf->Rect(9, 9, self::W - 18, 28, 'F');

        // Republic of the Philippines
        $this->setColor('text', self::GOLD);
        $this->pdf->SetFont('Times', 'I', 7.5);
        $this->pdf->SetXY(0, 12);
        $this->pdf->Cell(self::W, 0, 'Republic of the Philippines', 0, 0, 'C');

        // Org name
        $this->setColor('text', self::WHITE);
        $this->pdf->SetFont('Times', 'B', 11);
        $this->pdf->SetXY(0, 17);
        $this->pdf->Cell(self::W, 0, 'Technical Education and Skills Development Authority', 0, 0, 'C');

        // Tagline
        $this->setColor('text', self::GOLD);
        $this->pdf->SetFont('Times', 'I', 7);
        $this->pdf->SetXY(0, 24);
        $this->pdf->Cell(self::W, 0, '"Empowering the Filipino Workforce"', 0, 0, 'C');

        // Left logo
        $logoPath = public_path('assets/app_logo.png');
        if (file_exists($logoPath)) {
            $this->pdf->Image($logoPath, 11, 11, 18, 18);
        }

        // Right logo
        if (file_exists($logoPath)) {
            $this->pdf->Image($logoPath, self::W - 29, 11, 18, 18);
        }
    }

    protected function drawTesdaBadge(): void
    {
        $this->setColor('text', self::GOLD);
        $this->pdf->SetFont('Times', 'B', 8);
        $this->pdf->SetXY(0, 41);
        $this->pdf->Cell(self::W, 0, '-  TESDA CERTIFIED  -', 0, 0, 'C');
    }

    protected function drawCertificateTitle(): void
    {
        $this->setColor('text', self::DGRAY);
        $this->pdf->SetFont('Times', 'I', 13);
        $this->pdf->SetXY(0, 47);
        $this->pdf->Cell(self::W, 0, '', 0, 0, 'C');

        $this->setColor('text', self::NAVY);
        $this->pdf->SetFont('Times', 'B', 26);
        $this->pdf->SetXY(0, 54);
        $this->pdf->Cell(self::W, 0, ' Certificate of Completion', 0, 0, 'C');
    }

    protected function drawDivider(float $y): void
    {
        $cx      = self::W / 2;
        $halfLen = 55;

        $this->setColor('draw', self::GOLD);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Line($cx - $halfLen, $y, $cx + $halfLen, $y);

        $this->setColor('fill', self::GOLD);
        $this->pdf->Circle($cx,            $y, 0.8, 'F');
        $this->pdf->Circle($cx - $halfLen, $y, 0.5, 'F');
        $this->pdf->Circle($cx + $halfLen, $y, 0.5, 'F');
    }

    protected function drawBody(): void
    {
        $cert          = $this->certificate;
        $trainee       = $cert->enrollment->trainee->name;
        $course        = $cert->enrollment->course->name;
        $level         = $cert->enrollment->course->level;
        $hours         = $cert->enrollment->course->duration_hours;
        $courseDisplay = $course . ($level ? " ({$level})" : '');

        $this->setColor('text', self::DGRAY);
        $this->pdf->SetFont('Times', 'I', 9.5);
        $this->pdf->SetXY(0, 110);
        $this->pdf->Cell(self::W, 0, 'This is to certify that', 0, 0, 'C');

        $this->setColor('text', self::NAVY);
        $this->pdf->SetFont('Times', 'BI', 22);
        $this->pdf->SetXY(0, 117);
        $this->pdf->Cell(self::W, 0, $trainee, 0, 0, 'C');

        $nameWidth = $this->pdf->GetStringWidth($trainee) + 30;
        $cx        = self::W / 2;
        $this->setColor('draw', self::NAVY);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Line($cx - $nameWidth / 2, 128, $cx + $nameWidth / 2, 128);

        $this->setColor('text', self::DGRAY);
        $this->pdf->SetFont('Times', '', 9);
        $this->pdf->SetXY(0, 131);
        $this->pdf->Cell(self::W, 0, 'has successfully completed the required training and competency assessment for', 0, 0, 'C');

        $this->setColor('text', self::NAVY);
        $this->pdf->SetFont('Times', 'B', 13);
        $this->pdf->SetXY(0, 138);
        $this->pdf->Cell(self::W, 0, $courseDisplay, 0, 0, 'C');

        $this->setColor('text', self::DGRAY);
        $this->pdf->SetFont('Times', 'I', 8.5);
        $this->pdf->SetXY(0, 146);
        $this->pdf->Cell(self::W, 0, "with a total of {$hours} training hours, and is hereby awarded this certificate in recognition of successful completion.", 0, 0, 'C');
    }

    protected function drawDetailsRow(): void
    {
        $cert       = $this->certificate;
        $issued     = $cert->issued_at->format('F d, Y');
        $validUntil = $cert->expires_at ? $cert->expires_at->format('F d, Y') : 'No Expiry';

        $items = [
            ['label' => 'DATE ISSUED', 'value' => $issued],
            ['label' => 'VALID UNTIL', 'value' => $validUntil],
        ];

        $boxW   = 60;
        $gap    = 20;
        $total  = count($items) * $boxW + (count($items) - 1) * $gap;
        $startX = (self::W - $total) / 2;

        foreach ($items as $i => $item) {
            $x = $startX + $i * ($boxW + $gap);
            $y = 174;

            $this->setColor('draw', self::GOLD);
            $this->pdf->SetLineWidth(0.5);
            $this->pdf->Line($x, $y, $x + $boxW, $y);

            $this->setColor('text', self::NAVY);
            $this->pdf->SetFont('Times', 'B', 9);
            $this->pdf->SetXY($x, $y + 1);
            $this->pdf->Cell($boxW, 5, $item['value'], 0, 0, 'C');

            $this->setColor('text', self::DGRAY);
            $this->pdf->SetFont('Times', '', 6.5);
            $this->pdf->SetXY($x, $y + 6);
            $this->pdf->Cell($boxW, 4, $item['label'], 0, 0, 'C');
        }
    }

    protected function drawSignatures(): void
    {
        $cert        = $this->certificate;
        $trainerName = $cert->trainer?->name ?? 'Trainer / Assessor';

        $sigs = [
            ['name' => 'Training Center Director', 'title' => 'Authorized Signatory'],
            ['name' => 'TESDA Regional Director',  'title' => 'TESDA Authority'],
            ['name' => $trainerName,               'title' => 'Trainer / Assessor'],
        ];

        $lineW  = 60;
        $gap    = 15;
        $total  = count($sigs) * $lineW + (count($sigs) - 1) * $gap;
        $startX = (self::W - $total) / 2;
        $y      = 191;

        foreach ($sigs as $i => $sig) {
            $x = $startX + $i * ($lineW + $gap);

            $this->setColor('draw', self::NAVY);
            $this->pdf->SetLineWidth(0.3);
            $this->pdf->Line($x, $y, $x + $lineW, $y);

            $this->setColor('text', self::NAVY);
            $this->pdf->SetFont('Times', 'B', 7.5);
            $this->pdf->SetXY($x, $y + 1);
            $this->pdf->Cell($lineW, 4, $sig['name'], 0, 0, 'C');

            $titleText = strlen($sig['title']) > 35
                ? substr($sig['title'], 0, 32) . '...'
                : $sig['title'];

            $this->setColor('text', self::DGRAY);
            $this->pdf->SetFont('Times', 'I', 6.5);
            $this->pdf->SetXY($x, $y + 5);
            $this->pdf->Cell($lineW, 3, $titleText, 0, 0, 'C');
        }
    }

    protected function drawCertNumber(): void
    {
        $num = $this->certificate->certificate_number;
        $this->setColor('text', [160, 160, 160]);
        $this->pdf->SetFont('Times', '', 6.5);
        $this->pdf->SetXY(0, self::H - 8);
        $this->pdf->Cell(self::W, 0, 'Certificate No: ' . $num, 0, 0, 'C');
    }
}