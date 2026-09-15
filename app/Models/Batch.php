<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Batch extends Model
{
    protected $fillable = [
        'event_id',
        'ticket_type_id',
        'batch_timestamp',
        'ticket_count',
        'status',
        'zip_path',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class)
            ->where('batch_path', $this->batch_path);
    }

    public function getBatchPathAttribute(): string
    {
        return "tickets/{$this->event_id}/{$this->ticket_type_id}/batch-{$this->batch_timestamp}";
    }

    public function getBatchDirectoryAttribute(): string
    {
        return Storage::disk('local')->path($this->batch_path);
    }

    public function getFormattedDateAttribute(): string
    {
        return now()->setTimestamp($this->batch_timestamp)->format('M j, Y g:i A');
    }

    public function getIndividualPdfsAttribute(): array
    {
        $dir = $this->batch_directory;

        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir.'/ticket-*.pdf');

        usort($files, function ($a, $b) {
            return (int) basename($a, '.pdf') - (int) basename($b, '.pdf');
        });

        return $files;
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeForEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public static function deriveFromBatchPath(string $batchPath): ?array
    {
        $parts = explode('/', $batchPath);

        if (count($parts) < 4) {
            return null;
        }

        $eventId = (int) $parts[1];
        $typeId = (int) $parts[2];
        $timestamp = (int) Str::after($parts[3], 'batch-');

        if (! $eventId || ! $typeId || ! $timestamp) {
            return null;
        }

        return compact('eventId', 'typeId', 'timestamp');
    }
}
