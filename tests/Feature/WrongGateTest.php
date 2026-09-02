<?php

namespace Tests\Feature;

use App\Actions\CheckInTicket;
use App\Actions\GenerateTicketToken;
use App\Models\Event;
use App\Models\EventOperatorAssignment;
use App\Models\ScanLog;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WrongGateTest extends TestCase
{
    use RefreshDatabase;

    private function createEventWithTypes(): array
    {
        $event = Event::create([
            'name' => 'Test Event',
            'date' => now(),
            'time' => now(),
            'venue' => 'Venue',
            'capacity' => 100,
        ]);

        $ordinary = TicketType::create([
            'event_id' => $event->id,
            'name' => 'Ordinary',
            'quantity' => 50,
            'price' => 0,
        ]);

        $vip = TicketType::create([
            'event_id' => $event->id,
            'name' => 'VIP',
            'quantity' => 50,
            'price' => 0,
        ]);

        return compact('event', 'ordinary', 'vip');
    }

    private function createOperator(): User
    {
        Role::findOrCreate('operator');

        return User::factory()->create()->assignRole('operator');
    }

    private function createTicket(TicketType $type, int $eventId): Ticket
    {
        $tokenGenerator = app(GenerateTicketToken::class);
        $token = $tokenGenerator->generate($eventId);

        return Ticket::create([
            'ticket_type_id' => $type->id,
            'token' => $token,
            'short_code' => strtoupper(substr(md5($token), 0, 12)),
            'status' => 'generated',
        ]);
    }

    public function test_operator_assigned_to_ordinary_can_scan_ordinary_ticket(): void
    {
        ['event' => $event, 'ordinary' => $ordinary] = $this->createEventWithTypes();
        $operator = $this->createOperator();

        EventOperatorAssignment::create([
            'event_id' => $event->id,
            'user_id' => $operator->id,
            'ticket_type_id' => $ordinary->id,
            'gate_name' => 'Gate 1',
        ]);

        $ticket = $this->createTicket($ordinary, $event->id);

        $result = app(CheckInTicket::class)->handle(
            $ticket->token,
            $event->id,
            $operator,
        );

        $this->assertSame('ok', $result['status']);
    }

    public function test_operator_assigned_to_ordinary_cannot_scan_vip_ticket(): void
    {
        ['event' => $event, 'vip' => $vip] = $this->createEventWithTypes();
        $operator = $this->createOperator();

        EventOperatorAssignment::create([
            'event_id' => $event->id,
            'user_id' => $operator->id,
            'ticket_type_id' => $vip->id,
            'gate_name' => 'Gate 1',
        ]);

        $ordinary = TicketType::where('event_id', $event->id)->where('name', 'Ordinary')->first();
        $ticket = $this->createTicket($ordinary, $event->id);

        $result = app(CheckInTicket::class)->handle(
            $ticket->token,
            $event->id,
            $operator,
        );

        $this->assertSame('wrong_gate', $result['status']);
        $this->assertNull($ticket->fresh()->used_at);
        $this->assertSame(1, ScanLog::where('result', 'wrong_gate')->count());
    }

    public function test_admin_can_scan_any_ticket_type(): void
    {
        ['event' => $event, 'vip' => $vip] = $this->createEventWithTypes();

        Role::findOrCreate('admin');
        $admin = User::factory()->create()->assignRole('admin');

        $ticket = $this->createTicket($vip, $event->id);

        $result = app(CheckInTicket::class)->handle(
            $ticket->token,
            $event->id,
            $admin,
        );

        $this->assertSame('ok', $result['status']);
    }

    public function test_operator_without_assignment_can_scan_any_ticket(): void
    {
        ['event' => $event, 'vip' => $vip] = $this->createEventWithTypes();
        $operator = $this->createOperator();

        $ticket = $this->createTicket($vip, $event->id);

        $result = app(CheckInTicket::class)->handle(
            $ticket->token,
            $event->id,
            $operator,
        );

        $this->assertSame('ok', $result['status']);
    }

    public function test_wrong_gate_scan_logs_the_ticket_type(): void
    {
        ['event' => $event, 'ordinary' => $ordinary, 'vip' => $vip] = $this->createEventWithTypes();
        $operator = $this->createOperator();

        EventOperatorAssignment::create([
            'event_id' => $event->id,
            'user_id' => $operator->id,
            'ticket_type_id' => $ordinary->id,
            'gate_name' => 'Gate 1',
        ]);

        $ticket = $this->createTicket($vip, $event->id);

        $result = app(CheckInTicket::class)->handle(
            $ticket->token,
            $event->id,
            $operator,
        );

        $this->assertSame('wrong_gate', $result['status']);
        $this->assertSame('VIP', $result['ticket']->ticketType->name);
    }
}
