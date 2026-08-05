<?php

namespace App\Actions;

use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class GeneratePdfPreview
{
    public function generate(string $storagePath): ?string
    {
        $fullPath = Storage::disk('local')->path($storagePath);

        if (! file_exists($fullPath)) {
            return null;
        }

        $previewDir = 'previews';
        Storage::makeDirectory($previewDir);

        $hash = md5($storagePath);
        $outputPath = "{$previewDir}/{$hash}.png";

        $outputFull = Storage::disk('local')->path($outputPath);

        if (file_exists($outputFull)) {
            $this->resizeToMaxWidth($outputFull, 500);
            return $outputPath;
        }

        $cmd = sprintf(
            'gs -dNOPAUSE -dBATCH -dSAFER -sDEVICE=png16m -r72 -dFirstPage=1 -dLastPage=1 -dTextAlphaBits=4 -dGraphicsAlphaBits=4 -sOutputFile=%s %s 2>/dev/null',
            escapeshellarg($outputFull),
            escapeshellarg($fullPath),
        );

        shell_exec($cmd);

        if (! file_exists($outputFull)) {
            return null;
        }

        $this->resizeToMaxWidth($outputFull, 500);

        return $outputPath;
    }

    private function resizeToMaxWidth(string $path, int $maxWidth): void
    {
        $info = @getimagesize($path);
        if (! $info) {
            return;
        }

        [$origW, $origH] = $info;

        if ($origW <= $maxWidth) {
            return;
        }

        $newW = $maxWidth;
        $newH = (int) round($origH * $maxWidth / $origW);

        $src = @imagecreatefrompng($path);
        if (! $src) {
            return;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagepng($dst, $path);
    }

    public function getPageDimensions(string $storagePath): ?array
    {
        $fullPath = Storage::disk('local')->path($storagePath);

        if (! file_exists($fullPath)) {
            return null;
        }

        try {
            $pdf = new Fpdi;
            $pageCount = $pdf->setSourceFile($fullPath);
            $templateId = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($templateId);

            return [
                'width' => round($size['width'], 2),
                'height' => round($size['height'], 2),
                'orientation' => $size['orientation'],
            ];
        } catch (\Exception) {
            return null;
        }
    }
}
