<?php

namespace App\Actions;

use App\Models\Batch;
use setasign\Fpdi\Fpdi;

class GeneratePrintSheet
{
    private const A4_WIDTH = 210;

    private const A4_HEIGHT = 297;

    private const MARGIN = 5;

    public function handle(Batch $batch): string
    {
        $pdfs = $batch->individual_pdfs;

        if (empty($pdfs)) {
            throw new \RuntimeException('No individual ticket PDFs found for this batch.');
        }

        $outputPath = tempnam(sys_get_temp_dir(), 'print_sheet_').'.pdf';

        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetAutoPageBreak(false);
        $pdf->SetMargins(0, 0, 0);

        $currentY = 0;
        $pageStarted = false;
        $usableWidth = self::A4_WIDTH - (self::MARGIN * 2);

        foreach ($pdfs as $pdfPath) {
            if (! file_exists($pdfPath)) {
                continue;
            }

            try {
                $pageCount = $pdf->setSourceFile($pdfPath);
                $templateId = $pdf->importPage(1);
                $size = $pdf->getTemplateSize($templateId);

                $ticketWidth = $size['width'];
                $ticketHeight = $size['height'];

                $scaleFactor = $usableWidth / $ticketWidth;
                $scaledHeight = $ticketHeight * $scaleFactor;

                $availableHeight = self::A4_HEIGHT - $currentY;

                if (($scaledHeight + 2) > $availableHeight) {
                    $pdf->AddPage();
                    $currentY = 0;
                    $pageStarted = true;
                }

                if (! $pageStarted) {
                    $pdf->AddPage();
                    $pageStarted = true;
                }

                $pdf->useTemplate($templateId, self::MARGIN, $currentY, $usableWidth, $scaledHeight);

                $currentY += $scaledHeight + 2;
            } catch (\Throwable) {
                continue;
            }
        }

        $pdf->Output('F', $outputPath);

        return $outputPath;
    }
}
