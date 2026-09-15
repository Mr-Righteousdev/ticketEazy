<?php

use App\Models\Batch;
use App\Models\Ticket;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        $batchPaths = Ticket::query()
            ->whereNotNull('batch_path')
            ->distinct()
            ->pluck('batch_path');

        foreach ($batchPaths as $batchPath) {
            $derived = Batch::deriveFromBatchPath($batchPath);

            if (! $derived) {
                continue;
            }

            $ticketCount = Ticket::where('batch_path', $batchPath)->count();

            $zipRelative = dirname($batchPath)."/downloads/{$derived['typeId']}-batch-{$derived['timestamp']}.zip";
            $zipExists = Storage::disk('local')->exists($zipRelative);

            Batch::create([
                'event_id' => $derived['eventId'],
                'ticket_type_id' => $derived['typeId'],
                'batch_timestamp' => $derived['timestamp'],
                'ticket_count' => $ticketCount,
                'status' => $zipExists ? 'ready' : 'generating',
                'zip_path' => $zipExists ? $zipRelative : null,
            ]);
        }
    }

    public function down(): void
    {
        Batch::query()->delete();
    }
};
