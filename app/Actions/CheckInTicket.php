<?php

namespace App\Actions;

use App\Models\EventOperatorAssignment;
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
     * @return array{status: 'ok'|'already_used'|'expired'|'invalid'|'wrong_gate', ticket?: Ticket}
     */
    public function handle(string $rawToken, int $eventId, User $operator, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $candidate = $this->normalizeToken($rawToken);

        if (GenerateTicketShortCode::isShortCode($candidate)) {
            $ticket = Ticket::where('short_code', $candidate)
                ->whereHas('ticketType', fn ($query) => $query->where('event_id', $eventId))
                ->first();

            return $this->processTicket($ticket, $eventId, $operator, $ipAddress, $userAgent);
        }

        if (! $this->tokenVerifier->verify($candidate, $eventId)) {
            return ['status' => 'invalid'];
        }

        $ticket = Ticket::where('token', $candidate)->first();

        return $this->processTicket($ticket, $eventId, $operator, $ipAddress, $userAgent);
    }

    /**
     * @return array{status: 'ok'|'already_used'|'expired'|'invalid'|'wrong_gate', ticket?: Ticket}
     */
    private function processTicket(?Ticket $ticket, int $eventId, User $operator, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        if (! $ticket) {
            return ['status' => 'invalid'];
        }

        // Check if operator is assigned to a different ticket type for this event
        if (! $operator->hasRole('admin')) {
            $assignment = EventOperatorAssignment::where('event_id', $eventId)
                ->where('user_id', $operator->id)
                ->first();

            if ($assignment && $assignment->ticket_type_id !== $ticket->ticket_type_id) {
                $ticket->load('ticketType');

                ScanLog::create([
                    'ticket_id' => $ticket->id,
                    'scanned_by' => $operator->id,
                    'scanned_at' => now(),
                    'result' => 'wrong_gate',
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                ]);

                return [
                    'status' => 'wrong_gate',
                    'ticket' => $ticket,
                ];
            }
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
