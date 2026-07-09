<?php

namespace App\Jobs;

use App\Actions\GenerateTicketToken;
use App\Models\Ticket;
use App\Models\TicketType;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use ZipArchive;

class GenerateTickets implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TicketType $ticketType,
        public int $quantity,
    ) {}

    public function handle(GenerateTicketToken $tokenGenerator): void
    {
        $ticketType = $this->ticketType;
        $eventId = $ticketType->event_id;
        $typeId = $ticketType->id;
        $quantity = min($this->quantity, $ticketType->quantity);

        $timestamp = now()->timestamp;
        $basePath = "tickets/{$eventId}/{$typeId}";
        $batchDir = "{$basePath}/batch-{$timestamp}";

        Storage::makeDirectory($batchDir);

        $pdfPaths = [];
        $templatePath = $ticketType->template_path
            ? Storage::disk('local')->path($ticketType->template_path)
            : null;

        $qrOptions = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false,
            'scale' => 10,
        ]);

        $qrCode = new QRCode($qrOptions);

        for ($i = 0; $i < $quantity; $i++) {
            $token = $tokenGenerator->generate($eventId);

            $ticket = Ticket::create([
                'ticket_type_id' => $typeId,
                'token' => $token,
                'status' => 'generated',
            ]);

            $shortToken = substr($token, 0, 12);
            $pdfFilename = "ticket-{$shortToken}.pdf";

            if ($templatePath && file_exists($templatePath)) {
                $this->stampPdf(
                    $qrCode,
                    $ticket,
                    $templatePath,
                    Storage::disk('local')->path("{$batchDir}/{$pdfFilename}"),
                );
                $pdfPaths[] = Storage::disk('local')->path("{$batchDir}/{$pdfFilename}");
            }
        }

        if (! empty($pdfPaths)) {
            $zipPath = Storage::disk('local')->path("{$basePath}/downloads");
            Storage::makeDirectory("{$basePath}/downloads");
            $zipFile = "{$zipPath}/{$typeId}-batch-{$timestamp}.zip";

            $zip = new ZipArchive;
            if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
                foreach ($pdfPaths as $path) {
                    $zip->addFile($path, basename($path));
                }
                $zip->close();
            }
        }
    }

    private function stampPdf(QRCode $qrCode, Ticket $ticket, string $templatePath, string $outputPath): void
    {
        $ticketType = $this->ticketType;
        $qrImage = $qrCode->render($ticket->token);

        $pdf = new Fpdi;
        $pageCount = $pdf->setSourceFile($templatePath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage(
                $size['orientation'] === 'L' ? 'L' : 'P',
                [$size['width'], $size['height']],
            );

            $pdf->useTemplate($templateId);

            $x = $ticketType->qr_x ?? 10;
            $y = $ticketType->qr_y ?? 10;
            $qrSize = $ticketType->qr_size ?? 30;

            $tempPath = tempnam(sys_get_temp_dir(), 'qr_').'.png';
            file_put_contents($tempPath, $qrImage);
            $pdf->Image($tempPath, $x, $y, $qrSize, $qrSize);
            unlink($tempPath);
        }

        $pdf->Output('F', $outputPath);
    }
}
