@php
    $event = $ticket->ticketType->event;

    $statusMeta = [
        'generated' => ['label' => 'Generated', 'classes' => 'bg-zinc-500/10 text-zinc-700 dark:bg-zinc-500/20 dark:text-zinc-300'],
        'sent' => ['label' => 'Sent', 'classes' => 'bg-sky-500/10 text-sky-700 dark:bg-sky-500/20 dark:text-sky-400'],
        'used' => ['label' => 'Used', 'classes' => 'bg-emerald-500/10 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400'],
        'expired' => ['label' => 'Expired', 'classes' => 'bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-400'],
        'failed' => ['label' => 'Failed', 'classes' => 'bg-red-500/10 text-red-700 dark:bg-red-500/20 dark:text-red-400'],
    ];

    $status = $statusMeta[$ticket->status] ?? [
        'label' => ucfirst($ticket->status),
        'classes' => 'bg-zinc-500/10 text-zinc-700 dark:bg-zinc-500/20 dark:text-zinc-300',
    ];

    $shortToken = substr($ticket->token, 0, 12);
    $price = number_format((float) $ticket->ticketType->price, 2);
@endphp

<div class="overflow-hidden rounded-2xl bg-white ring-1 ring-zinc-200/80 dark:bg-zinc-800 dark:ring-zinc-700/60">
    <div class="flex items-center justify-between gap-3 border-b border-zinc-100 px-5 py-3 dark:border-zinc-700/60">
        <span class="text-xs font-semibold text-zinc-400 dark:text-zinc-500">Ticket #{{ $ticket->id }}</span>
        <span class="{{ $status['classes'] }} rounded-full px-2.5 py-0.5 text-[11px] font-semibold">{{ $status['label'] }}</span>
    </div>

    <div class="relative grid grid-cols-1 md:grid-cols-2">
        <div class="px-5 py-5 md:border-r md:border-dashed md:border-zinc-200 md:dark:border-zinc-700">
            <p class="text-[10px] font-semibold tracking-[0.2em] uppercase text-emerald-600 dark:text-emerald-400">Event</p>
            <h2 class="mt-1 text-xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $event->name }}</h2>

            <span class="mt-2.5 inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">
                <flux:icon.ticket class="size-3.5" />
                {{ $ticket->ticketType->name }}
            </span>

            <dl class="mt-5 space-y-3.5 text-sm">
                <div class="flex items-center gap-3">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <flux:icon.calendar-days class="size-4" />
                    </span>
                    <div class="min-w-0">
                        <dt class="text-[10px] font-semibold tracking-wider uppercase text-zinc-400 dark:text-zinc-500">Date</dt>
                        <dd class="font-medium text-zinc-900 dark:text-zinc-200">{{ $event->date->format('M j, Y') }}</dd>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <flux:icon.clock class="size-4" />
                    </span>
                    <div class="min-w-0">
                        <dt class="text-[10px] font-semibold tracking-wider uppercase text-zinc-400 dark:text-zinc-500">Time</dt>
                        <dd class="font-medium text-zinc-900 dark:text-zinc-200">{{ $event->time?->format('g:i A') }}</dd>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <flux:icon.map-pin class="size-4" />
                    </span>
                    <div class="min-w-0">
                        <dt class="text-[10px] font-semibold tracking-wider uppercase text-zinc-400 dark:text-zinc-500">Venue</dt>
                        <dd class="truncate font-medium text-zinc-900 dark:text-zinc-200">{{ $event->venue }}</dd>
                    </div>
                </div>
                @if ($ticket->used_at)
                    <div class="flex items-center gap-3">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                            <flux:icon.minus-circle class="size-4" />
                        </span>
                        <div class="min-w-0">
                            <dt class="text-[10px] font-semibold tracking-wider uppercase text-zinc-400 dark:text-zinc-500">Used at</dt>
                            <dd class="font-medium text-zinc-900 dark:text-zinc-200">{{ $ticket->used_at->format('M j, Y g:i A') }}</dd>
                        </div>
                    </div>
                @endif
            </dl>
        </div>

        <div class="pointer-events-none absolute top-0 left-1/2 hidden size-5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-white ring-1 ring-zinc-200/80 md:block dark:bg-zinc-800 dark:ring-zinc-700/60"></div>
        <div class="pointer-events-none absolute bottom-0 left-1/2 hidden size-5 -translate-x-1/2 translate-y-1/2 rounded-full bg-white ring-1 ring-zinc-200/80 md:block dark:bg-zinc-800 dark:ring-zinc-700/60"></div>

        <div class="flex flex-col items-center justify-center gap-3 border-t border-dashed border-zinc-200 px-5 py-6 md:border-t-0 dark:border-zinc-700">
            <div class="rounded-xl bg-white p-3 ring-1 ring-zinc-200 dark:ring-zinc-700">
                <img src="{{ $qrDataUri }}" alt="Ticket QR code" class="size-36 md:size-40">
            </div>

            <p class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                <flux:icon.qr-code class="size-3.5" />
                Scan to verify
            </p>

            <p class="font-mono text-xs font-semibold text-zinc-500 dark:text-zinc-400" title="{{ $ticket->token }}">{{ $shortToken }}</p>
        </div>
    </div>

    <div class="flex items-center justify-between gap-3 border-t border-zinc-100 px-5 py-3.5 dark:border-zinc-700/60">
        <span class="flex items-center gap-1.5 text-xs text-zinc-400 dark:text-zinc-500">
            <flux:icon.shield-check class="size-3.5" />
            Verified ticket
        </span>
        @if ($ticket->batch_path)
            <span class="truncate text-[11px] text-zinc-400 dark:text-zinc-500" title="{{ $ticket->batch_path }}">{{ Str::afterLast($ticket->batch_path, '/') }}</span>
        @endif
        <span class="text-sm font-bold text-zinc-900 dark:text-white">${{ $price }}</span>
    </div>
</div>
