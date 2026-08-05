<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head', ['title' => __('Ticket Verification')])

        <style>
            @keyframes verify-pop {
                0% { opacity: 0; transform: scale(.7); }
                100% { opacity: 1; transform: scale(1); }
            }

            @keyframes verify-rise {
                0% { opacity: 0; transform: translateY(14px); }
                100% { opacity: 1; transform: translateY(0); }
            }

            .verify-pop { animation: verify-pop .45s cubic-bezier(.22, 1, .36, 1) .1s both; }
            .verify-rise { animation: verify-rise .45s cubic-bezier(.22, 1, .36, 1) both; }

            @media (prefers-reduced-motion: reduce) {
                .verify-pop, .verify-rise { animation: none; }
            }
        </style>
    </head>
    <body class="relative flex min-h-screen items-center justify-center overflow-hidden bg-gradient-to-br from-emerald-50 via-white to-emerald-100 px-4 py-12 dark:from-zinc-900 dark:via-zinc-950 dark:to-zinc-900">
        @php
            $found = (bool) $ticket;
            $valid = $found && in_array($ticket->status, ['generated', 'sent'], true);

            $statuses = [
                'generated' => ['label' => 'Valid Ticket', 'message' => 'This ticket is verified and ready for entry.'],
                'sent' => ['label' => 'Valid Ticket', 'message' => 'This ticket is verified and ready for entry.'],
                'used' => ['label' => 'Already Used', 'message' => 'This ticket has already been used.'],
                'expired' => ['label' => 'Expired', 'message' => 'This ticket has expired and can no longer be used.'],
                'failed' => ['label' => 'Invalid Ticket', 'message' => 'There was a problem with this ticket.'],
            ];

            $state = $found
                ? ($statuses[$ticket->status] ?? ['label' => 'Unknown Status', 'message' => 'This ticket has an unrecognised status.'])
                : ['label' => 'Ticket Not Found', 'message' => 'This QR code does not match any ticket in our system.'];

            $tone = $valid
                ? [
                    'badge' => 'from-emerald-400 to-emerald-600 text-white shadow-lg shadow-emerald-500/40 ring-emerald-100 dark:ring-emerald-500/20',
                    'chip' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
                    'kicker' => 'text-emerald-600 dark:text-emerald-400',
                    'glow' => 'bg-emerald-400/25',
                ]
                : [
                    'badge' => 'from-red-400 to-red-600 text-white shadow-lg shadow-red-500/40 ring-red-100 dark:ring-red-500/20',
                    'chip' => 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400',
                    'kicker' => 'text-red-600 dark:text-red-400',
                    'glow' => 'bg-red-400/25',
                ];
        @endphp

        <div class="pointer-events-none absolute -top-24 -right-24 size-80 rounded-full {{ $tone['glow'] }} blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -left-24 size-80 rounded-full {{ $tone['glow'] }} blur-3xl"></div>

        <main class="relative w-full max-w-md verify-rise">
            <div class="overflow-hidden rounded-[2rem] bg-white shadow-2xl shadow-emerald-900/10 ring-1 ring-zinc-200/80 dark:bg-zinc-900 dark:shadow-black/40 dark:ring-zinc-800">
                <div class="flex items-center justify-between gap-3 border-b border-dashed border-zinc-200 px-6 py-4 dark:border-zinc-800">
                    <span class="flex items-center gap-2">
                        <x-app-logo-icon class="size-5 {{ $tone['kicker'] }}" />
                        <span class="text-sm font-bold tracking-tight text-zinc-900 dark:text-white">{{ config('app.name') }}</span>
                    </span>
                    <span class="text-[10px] font-semibold tracking-[0.22em] text-zinc-500 uppercase dark:text-zinc-400">Ticket Verification</span>
                </div>

                <div class="px-6 pt-8 pb-6 text-center">
                    <div class="verify-pop mx-auto mb-5 flex size-24 items-center justify-center rounded-full bg-gradient-to-br {{ $tone['badge'] }}">
                        @if ($valid)
                            <flux:icon.check class="size-12" />
                        @else
                            <flux:icon.x-circle class="size-12" />
                        @endif
                    </div>

                    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $state['label'] }}</h1>
                    <p class="mx-auto mt-1.5 max-w-xs text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">{{ $state['message'] }}</p>
                </div>

                @if ($found)
                    @php
                        $event = $ticket->ticketType->event;
                    @endphp

                    <div class="relative px-6 pb-6">
                        <div class="relative rounded-2xl bg-zinc-50 p-5 ring-1 ring-zinc-100 dark:bg-zinc-800/60 dark:ring-zinc-800">
                            <div class="pointer-events-none absolute top-1/2 -left-3 size-6 -translate-y-1/2 rounded-full bg-white dark:bg-zinc-900"></div>
                            <div class="pointer-events-none absolute top-1/2 -right-3 size-6 -translate-y-1/2 rounded-full bg-white dark:bg-zinc-900"></div>

                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[10px] font-semibold tracking-[0.2em] text-zinc-500 uppercase dark:text-zinc-400">Event</p>
                                    <h2 class="mt-1 truncate text-xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $event->name }}</h2>
                                </div>
                                <span class="{{ $tone['chip'] }} flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold">
                                    <flux:icon.ticket class="size-3.5" />
                                    {{ $ticket->ticketType->name }}
                                </span>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-4 border-t border-dashed border-zinc-200 pt-4 dark:border-zinc-700">
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1 text-[10px] font-semibold tracking-wider text-zinc-500 uppercase dark:text-zinc-400">
                                        <flux:icon.calendar-days class="size-3.5" /> Date
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $event->date->format('M j, Y') }}</p>
                                </div>
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1 text-[10px] font-semibold tracking-wider text-zinc-500 uppercase dark:text-zinc-400">
                                        <flux:icon.clock class="size-3.5" /> Time
                                    </p>
                                    <p class="mt-1 text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $event->time?->format('g:i A') }}</p>
                                </div>
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1 text-[10px] font-semibold tracking-wider text-zinc-500 uppercase dark:text-zinc-400">
                                        <flux:icon.map-pin class="size-3.5" /> Venue
                                    </p>
                                    <p class="mt-1 truncate text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $event->venue }}</p>
                                </div>
                            </div>
                        </div>

                        <dl class="mt-5 space-y-2.5 text-sm">
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-zinc-500 dark:text-zinc-400">Ticket type</dt>
                                <dd class="font-semibold text-zinc-900 dark:text-white">{{ $ticket->ticketType->name }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-zinc-500 dark:text-zinc-400">Ticket number</dt>
                                <dd class="font-mono text-xs font-semibold text-zinc-900 dark:text-white">{{ substr($ticket->token, 0, 12) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-zinc-500 dark:text-zinc-400">Status</dt>
                                <dd class="font-semibold {{ $tone['kicker'] }}">{{ $state['label'] }}</dd>
                            </div>
                            @if ($ticket->used_at)
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-zinc-500 dark:text-zinc-400">Used at</dt>
                                    <dd class="font-semibold text-zinc-900 dark:text-white">{{ $ticket->used_at->format('M j, Y g:i A') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @else
                    <div class="px-6 pb-8 text-center">
                        <div class="rounded-2xl bg-zinc-50 p-5 text-sm leading-relaxed text-zinc-500 ring-1 ring-zinc-100 dark:bg-zinc-800/60 dark:text-zinc-400 dark:ring-zinc-800">
                            Please check the QR code is intact, then try again. If the problem continues, contact the venue for assistance.
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3 border-t border-zinc-100 px-6 py-4 dark:border-zinc-800">
                    <span class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                        <flux:icon.shield-check class="size-3.5" />
                        Verified securely
                    </span>
                    <span class="text-xs tabular-nums text-zinc-500 dark:text-zinc-400">{{ now()->format('M j, Y g:i A') }}</span>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-zinc-500 dark:text-zinc-400">
                @if ($found)
                    Verified {{ $valid ? 'on' : 'against' }} {{ $event->name ?? 'this ticket' }} · {{ config('app.name') }}
                @else
                    Verification powered by {{ config('app.name') }}
                @endif
            </p>
        </main>
    </body>
</html>
