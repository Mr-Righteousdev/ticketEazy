<div class="space-y-4 p-4">
    <div class="flex justify-center">
        <img src="{{ $qrDataUri }}" alt="QR Code" class="w-48 h-48">
    </div>
    <dl class="space-y-2 text-sm">
        <div class="flex justify-between">
            <dt class="text-gray-500">Event</dt>
            <dd class="font-medium">{{ $ticket->ticketType->event->name }}</dd>
        </div>
        <div class="flex justify-between">
            <dt class="text-gray-500">Ticket Type</dt>
            <dd class="font-medium">{{ $ticket->ticketType->name }}</dd>
        </div>
        <div class="flex justify-between">
            <dt class="text-gray-500">Status</dt>
            <dd class="font-medium">{{ ucfirst($ticket->status) }}</dd>
        </div>
        <div class="flex justify-between">
            <dt class="text-gray-500">Token</dt>
            <dd class="font-mono text-xs break-all max-w-xs text-right">{{ $ticket->token }}</dd>
        </div>
        @if ($ticket->used_at)
            <div class="flex justify-between">
                <dt class="text-gray-500">Used At</dt>
                <dd class="font-medium">{{ $ticket->used_at->format('M j, Y g:i A') }}</dd>
            </div>
        @endif
    </dl>
</div>
