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
        $token = $this->normalizeToken($rawToken);

        if (! $this->tokenVerifier->verify($token, $eventId)) {
            return ['status' => 'invalid'];
        }

        return DB::transaction(function () use ($token, $operator, $ipAddress, $userAgent) {
            $ticket = Ticket::where('token', $token)
                ->lockForUpdate()
                ->first();

            if (! $ticket) {
                return ['status' => 'invalid'];
            }

            $result = match ($ticket->status) {
                'used' => 'already_used',
                'failed', 'expired' => 'expired',
                default => 'ok',
            };

            if ($result === 'ok') {
                $ticket->update([
                    'status' => 'used',
                    'used_at' => now(),
                    'scanned_by' => $operator->id,
                ]);
            }

            ScanLog::create([
                'ticket_id' => $ticket->id,
                'scanned_by' => $operator->id,
                'scanned_at' => now(),
                'result' => $result,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            return [
                'status' => $result,
                'ticket' => $ticket->load('ticketType.event'),
            ];
        });
    }

    private function normalizeToken(string $rawToken): string
    {
        if (preg_match('~/verify/([^/?#]+)~', $rawToken, $matches)) {
            return $matches[1];
        }

        return $rawToken;
    }
}
