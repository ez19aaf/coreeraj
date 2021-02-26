<?php


namespace Survey54\Reap\Application\Helper;

use FPDF;
use Survey54\Library\Domain\Values\QuestionType;

class PdfHelper extends FPDF
{
    private const WEBSITE = 'https://www.survey54.com';

    // Header Method
    public function header(): void
    {
        if ($this->PageNo() === 1) {
            // Logo
            $this->Image(__DIR__ . '/../assets/images/logo_small_width_200px.png', 10, 6, 40, 7, "", self::WEBSITE);
            // Arial bold 15
            $this->SetFont('Arial', 'B', 15);
            // Move to the right
            $this->Cell(80);
            // Line break
            $this->Ln(20);
        }
    }

    // Footer method
    public function footer(): void
    {
        $currentYear = date('Y');
        if ($this->PageNo() !== 1) {
            // Go to 1.5 cm from bottom
            $this->SetY(-10);
            // Select Arial italic 8
            $this->SetFont('Arial', 'I', 8);
            // Print centered page number
            $this->Image(__DIR__ . '/../assets/images/favicon.png', 5, null, 7, 7, "", self::WEBSITE);
            // Move to the right
            $this->Cell(28);
            // Title
            $this->SetY(-12);
            $this->SetX(15);
            $this->Cell(20, 10, 'Survey conducted by Survey54', 0, 0, 'L', false, self::WEBSITE);
            $this->SetX(100);
            $this->Cell(20, 10, iconv("UTF-8", "ISO-8859-1", "©") . ' ' . $currentYear, 0, 0, 'C');
            $this->SetX(175);
            $this->Cell(30, 10, 'https://www.survey54.com', 0, 0, 'R', false, self::WEBSITE);
        }
    }

    /**
     * @param $header
     * @param $data
     * @param $y
     */
    public function questionTable($header, $data, $y = 25): void
    {
        $this->SetY($y);
        $this->SetLeftMargin(15);

        // Colors, line width and bold font
        $this->SetFillColor(153, 204, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(221, 221, 221);
        $this->SetLineWidth(.3);
        $this->SetFont('Arial', '');
        // Header
        $w = [100, 40, 30];
        for ($i = 0, $iMax = count($header); $i < $iMax; $i++) {
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();
        // Data
        foreach ($data as $row) {
            $this->Cell($w[0], 6, $row[0], 1, 0, 'L', true);
            $this->Cell($w[1], 6, $row[1], 1, 0, 'L', true);
            $this->Cell($w[2], 6, $row[2], 1, 0, 'R', true);
            $this->Ln();
        }
    }

    private function sector($xc, $yc, $r, $a, $b): void
    {
        $d0 = $a - $b;
        $b  += 90;
        $a  += 90;
        while ($a < 0) {
            $a += 360;
        }
        while ($a > 360) {
            $a -= 360;
        }
        while ($b < 0) {
            $b += 360;
        }
        while ($b > 360) {
            $b -= 360;
        }
        if ($a > $b) {
            $b += 360;
        }
        $b = $b / 360 * 2 * M_PI;
        $a = $a / 360 * 2 * M_PI;
        $d = $b - $a;
        if ($d === 0 && $d0 !== 0) {
            $d = 2 * M_PI;
        }
        $k  = $this->k;
        $hp = $this->h;
        if (sin($d / 2)) {
            $MyArc = 4 / 3 * (1 - cos($d / 2)) / sin($d / 2) * $r;
        } else {
            $MyArc = 0;
        }
        //first put the center
        $this->_out(sprintf('%.2F %.2F m', ($xc) * $k, ($hp - $yc) * $k));
        //put the first point
        $this->_out(sprintf('%.2F %.2F l', ($xc + $r * cos($a)) * $k, (($hp - ($yc - $r * sin($a))) * $k)));
        //draw the arc
        if ($d < M_PI / 2) {
            $this->arc($a, $b, $xc, $yc, $r, $MyArc);
        } else {
            $b     = $a + $d / 4;
            $MyArc = 4 / 3 * (1 - cos($d / 8)) / sin($d / 8) * $r;
            $this->arc($a, $b, $xc, $yc, $r, $MyArc);

            $a = $b;
            $b = $a + $d / 4;
            $this->arc($a, $b, $xc, $yc, $r, $MyArc);

            $a = $b;
            $b = $a + $d / 4;
            $this->arc($a, $b, $xc, $yc, $r, $MyArc);

            $a = $b;
            $b = $a + $d / 4;
            $this->arc($a, $b, $xc, $yc, $r, $MyArc);
        }
        $op = 'b';
        $this->_out($op);
    }

    private function arc($a, $b, $xc, $yc, $r, $MyArc): void
    {
        $x1 = $xc + $r * cos($a) + $MyArc * cos(M_PI / 2 + $a);
        $y1 = $yc - $r * sin($a) - $MyArc * sin(M_PI / 2 + $a);
        $x2 = $xc + $r * cos($b) + $MyArc * cos($b - M_PI / 2);
        $y2 = $yc - $r * sin($b) - $MyArc * sin($b - M_PI / 2);
        $x3 = $xc + $r * cos($b);
        $y3 = $yc - $r * sin($b);
        $h  = $this->h;

        $this->_out(sprintf(
            '%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1 * $this->k,
            ($h - $y1) * $this->k,
            $x2 * $this->k,
            ($h - $y2) * $this->k,
            $x3 * $this->k,
            ($h - $y3) * $this->k
        ));
    }

    /**
     * @param $data
     * @param $questionType
     * @param $y
     */
    public function createChartPdf($data, $questionType, $y): void
    {
        switch ($questionType) {
            case QuestionType::SCALE:
                $valX = $this->GetX();
                $valY = $this->GetY();
                $this->SetXY(20, $valY);
                $this->pieChart(200, 70, $data, '%l (%p)');
                $this->SetXY($valX, $valY + 40);
                break;
            case QuestionType::MULTIPLE_CHOICE:
            case QuestionType::SINGLE_CHOICE:
                $this->barDiagram($data, $y);
                break;
        }
    }

    private function pieChart($w, $h, $data, $format, $colors = null): void
    {
        $this->SetFont('Courier', '', 10);

        //Set Legends
        $legends = [];
        $wLegend = 0;
        $sum     = array_sum($data);
        $NbVal   = count($data);
        foreach ($data as $l => $val) {
            $p         = sprintf('%.2f', $val / $sum * 100) . '%';
            $legend    = str_replace(['%l', '%v', '%p'], [$l, $val, $p], $format);
            $legends[] = $legend;
            $wLegend   = max($this->GetStringWidth($legend), $wLegend);
        }

        $XPage   = $this->GetX();
        $YPage   = $this->GetY();
        $margin  = 2;
        $hLegend = 5;
        $radius  = min($w - $margin * 4 - $hLegend - $wLegend, $h - $margin * 2);
        $radius  = floor($radius / 2);
        $XDiag   = $XPage + $margin + $radius;
        $YDiag   = $YPage + $margin + $radius;
        if ($colors === null) {
            for ($i = 0; $i < $NbVal; $i++) {
                $gray       = $i * (int)(255 / $NbVal);
                $colors[$i] = [100, ($gray % 255), 255];
            }
        }
        //Sectors
        $this->SetLineWidth(0.2);
        $angleStart = 0;
        $i          = 0;
        foreach ($data as $val) {
            $angle = ($val * 360) / (float)$sum;
            if ($angle !== 0) {
                $angleEnd = $angleStart + $angle;
                $this->SetFillColor($colors[$i][0], $colors[$i][1], $colors[$i][2]);
                $this->sector($XDiag, $YDiag, $radius, $angleStart, $angleEnd);
                $angleStart += $angle;
            }
            $i++;
        }
        //Legends
        $this->SetFont('Courier', '', 10);
        $x1 = $XPage + 2 * $radius + 4 * $margin;
        $x2 = $x1 + $hLegend + $margin;
        $y1 = $YDiag - $radius + (2 * $radius - $NbVal * ($hLegend + $margin)) / 2;
        for ($i = 0; $i < $NbVal; $i++) {
            $this->SetFillColor($colors[$i][0], $colors[$i][1], $colors[$i][2]);
            $this->Rect($x1, $y1, $hLegend, $hLegend, 'DF');
            $this->SetXY($x2, $y1);
            $this->Cell(0, $hLegend, $legends[$i]);
            $y1 += $hLegend + $margin;
        }
    }

    private function barDiagram($data, $y): void
    {
        $chartXPos    = 0;
        $chartYPos    = $y;
        $chartWidth   = 200;
        $chartHeight  = 50;
        $chartYStep   = 10;
        $rowLabels    = array_keys($data);
        $data         = array_values($data);
        $chartColours = [100, 100, 255];
        $xScale       = count($rowLabels) / ($chartWidth - 40);

        $maxTotal = 0;
        foreach ($data as $dataRow) {
            $maxTotal = ($dataRow > $maxTotal) ? $dataRow : $maxTotal;
        }
        $nextStep = $maxTotal % $chartYStep;
        $maxTotal += ($nextStep !== 0) ? $chartYStep - $nextStep : 0;

        $yScale   = ($maxTotal) / $chartHeight;
        $barWidth = (1 / $xScale) / 2.5;

        $this->SetFont('Arial', '', 10);
        $this->SetDrawColor(221, 221, 221);
        $this->Line($chartXPos + 20, $chartYPos, $chartXPos + $chartWidth, $chartYPos);
        foreach ($rowLabels as $i => $iValue) {
            $label = wordwrap($iValue, $barWidth, "<br />");
            $label = explode("<br />", $label);
            $y     = $chartYPos;
            foreach ($label as $text) {
                $this->SetXY($chartXPos + 25 + $i / $xScale, $y);
                $this->Cell($barWidth, 10, $text, 0, 0, 'C');
                $y += 5;
            }
        }

        $i = 0;
        do {
            $this->SetXY($chartXPos, $chartYPos - 5 - $i / $yScale);
            $this->Cell(20, 10, number_format($i) . '%', 0, 0, 'R');
            $this->Line($chartXPos + 20, $chartYPos - $i / $yScale, $chartXPos + 200, $chartYPos - $i / $yScale);
            $i += $chartYStep;
        } while ($i <= $maxTotal);

        $xPos = $chartXPos + 25;
        $i    = 0;
        foreach ($data as $dataRow) {
            $this->SetFillColor($chartColours[0], $chartColours[1], $chartColours[2]);
            $this->Rect($xPos, $chartYPos - ($dataRow / $yScale), $barWidth, $dataRow / $yScale, 'DF');
            $this->SetXY($chartXPos + 25 + $i / $xScale, $chartYPos - ($dataRow / $yScale) - 10);
            $this->Cell($barWidth, 10, $dataRow . "%", 0, 0, 'C');
            $xPos += (1 / $xScale);
            $i++;
        }
    }
}
