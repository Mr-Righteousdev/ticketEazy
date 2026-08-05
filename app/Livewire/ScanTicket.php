<?php

namespace App\Livewire;

use App\Actions\CheckInTicket;
use App\Models\Event;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ScanTicket extends Component
{
    public ?int $eventId = null;

    public string $result = '';

    public ?string $ticketTypeName = null;

    public ?string $eventName = null;

    public ?string $usedAt = null;

    public bool $scanning = true;

    public string $manualToken = '';

    public function mount(): void
    {
        $events = Event::all();

        if ($events->count() === 1) {
            $this->eventId = $events->first()->id;
        }
    }

    public function checkIn(string $rawToken): void
    {
        if (! $this->eventId) {
            return;
        }

        $result = app(CheckInTicket::class)->handle(
            $rawToken,
            $this->eventId,
            auth()->user(),
            request()->ip(),
            request()->userAgent(),
        );

        $ticket = $result['ticket'] ?? null;

        $this->result = $result['status'];
        $this->ticketTypeName = $ticket?->ticketType?->name;
        $this->eventName = $ticket?->ticketType?->event?->name;
        $this->usedAt = $ticket?->used_at?->format('H:i:s');
        $this->scanning = false;
        $this->manualToken = '';
    }

    public function manualCheckIn(): void
    {
        if (! trim($this->manualToken)) {
            return;
        }

        $this->checkIn(trim($this->manualToken));
    }

    public function resetScan(): void
    {
        $this->result = '';
        $this->ticketTypeName = null;
        $this->eventName = null;
        $this->usedAt = null;
        $this->scanning = true;

        $this->dispatch('scanner-reset');
    }

    public function render(): View
    {
        return view('livewire.scan-ticket', [
            'events' => Event::all(),
        ])->layout('layouts.app')->title('Scan Tickets');
    }
}
