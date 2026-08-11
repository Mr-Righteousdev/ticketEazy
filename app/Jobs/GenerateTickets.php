<?php

namespace App\Jobs;

use App\Actions\GenerateTicketShortCode;
use App\Actions\GenerateTicketToken;
use App\Models\Ticket;
use App\Models\TicketType;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use ZipArchive;

class GenerateTickets implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TicketType $ticketType,
        public int $quantity,
        public ?int $timestamp = null,
    ) {}

    public function handle(GenerateTicketToken $tokenGenerator, GenerateTicketShortCode $shortCodeGenerator): void
    {
        $ticketType = $this->ticketType;
        $eventId = $ticketType->event_id;
        $typeId = $ticketType->id;
        $quantity = min($this->quantity, $ticketType->quantity);

        $timestamp = $this->timestamp ?? now()->timestamp;
        $basePath = "tickets/{$eventId}/{$typeId}";
        $batchDir = "{$basePath}/batch-{$timestamp}";

        $templatePath = $ticketType->template_path
            ? Storage::disk('local')->path($ticketType->template_path)
            : null;

        if (! $templatePath || ! file_exists($templatePath)) {
            Log::warning("Ticket generation skipped for ticket type {$typeId}: no PDF template found.");

            return;
        }

        Storage::makeDirectory($batchDir);

        $pdfPaths = [];

        $qrOptions = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false,
            'scale' => 10,
            'eccLevel' => EccLevel::M,
        ]);

        for ($i = 0; $i < $quantity; $i++) {
            $token = $tokenGenerator->generate($eventId);
            $shortCode = $shortCodeGenerator->generate();

            $ticket = Ticket::create([
                'ticket_type_id' => $typeId,
                'token' => $token,
                'short_code' => $shortCode,
                'batch_path' => $batchDir,
                'status' => 'generated',
            ]);

            $pdfFilename = "ticket-{$ticket->id}.pdf";
            $outputPath = Storage::disk('local')->path("{$batchDir}/{$pdfFilename}");

            try {
                $this->stampPdf(new QRCode($qrOptions), $ticket, $templatePath, $outputPath);
                $pdfPaths[] = $outputPath;
            } catch (\Throwable $e) {
                $ticket->update(['status' => 'failed']);
                Log::error("Ticket {$ticket->id} PDF generation failed: ".$e->getMessage());
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

                Log::info("Ticket batch ready for ticket type {$typeId}: {$zipFile}");
            }
        }
    }

    private function stampPdf(QRCode $qrCode, Ticket $ticket, string $templatePath, string $outputPath): void
    {
        $ticketType = $this->ticketType;
        $qrImage = $qrCode->render(route('ticket.quick-verify', $ticket->short_code));

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
