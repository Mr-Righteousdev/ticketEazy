<?php

namespace App\Actions;

use App\Models\ScanLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckInTicket
{
    public function __construct(
        private GenerateTicketToken $tokenVerifier,
    ) {}

    /**
     * @return array{status: 'ok'|'already_used'|'expired'|'invalid', ticket?: Ticket}
     */
    public function handle(string $rawToken, int $eventId, User $operator, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $candidate = $this->normalizeToken($rawToken);

        if (GenerateTicketShortCode::isShortCode($candidate)) {
            $ticket = Ticket::where('short_code', $candidate)
                ->whereHas('ticketType', fn ($query) => $query->where('event_id', $eventId))
                ->first();

            return $this->processTicket($ticket, $operator, $ipAddress, $userAgent);
        }

        if (! $this->tokenVerifier->verify($candidate, $eventId)) {
            return ['status' => 'invalid'];
        }

        $ticket = Ticket::where('token', $candidate)->first();

        return $this->processTicket($ticket, $operator, $ipAddress, $userAgent);
    }

    /**
     * @return array{status: 'ok'|'already_used'|'expired'|'invalid', ticket?: Ticket}
     */
    private function processTicket(?Ticket $ticket, User $operator, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        if (! $ticket) {
            return ['status' => 'invalid'];
        }

        return DB::transaction(function () use ($ticket, $operator, $ipAddress, $userAgent) {
            $locked = Ticket::whereKey($ticket->id)
                ->lockForUpdate()
                ->first();

            $result = match ($locked->status) {
                'used' => 'already_used',
                'failed', 'expired' => 'expired',
                default => 'ok',
            };

            if ($result === 'ok') {
                $locked->update([
                    'status' => 'used',
                    'used_at' => now(),
                    'scanned_by' => $operator->id,
                ]);
            }

            ScanLog::create([
                'ticket_id' => $locked->id,
                'scanned_by' => $operator->id,
                'scanned_at' => now(),
                'result' => $result,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return [
                'status' => $result,
                'ticket' => $locked->load('ticketType.event'),
            ];
        });
    }

    private function normalizeToken(string $rawToken): string
    {
        if (preg_match('~/(?:v|verify)/([^/?#]+)~', $rawToken, $matches)) {
            return $matches[1];
        }

        return $rawToken;
    }
}
