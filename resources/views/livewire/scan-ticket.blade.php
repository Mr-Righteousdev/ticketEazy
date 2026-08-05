@assets
    @vite('resources/js/scan.js')
@endassets

<div class="mx-auto flex w-full max-w-md flex-col gap-6 py-8">
    <div class="text-center">
        <flux:heading size="xl">Ticket Check-in</flux:heading>
        <flux:subheading>Point the camera at a ticket QR code.</flux:subheading>
    </div>

    @if ($events->count() > 1)
        <div>
            <label for="event" class="mb-1 block text-sm font-medium text-zinc-500 dark:text-zinc-400">Event</label>
            <select wire:model="eventId" id="event" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                <option value="">-- Choose event --</option>
                @foreach ($events as $event)
                    <option value="{{ $event->id }}">{{ $event->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    @if (! $eventId)
        <p class="text-center text-sm text-zinc-400">Select an event to start scanning.</p>
    @endif

    @if ($scanning && $eventId)
        <div id="qr-reader" class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700"></div>
        <p class="text-center text-sm text-zinc-400">Point your camera at the ticket QR code.</p>
    @endif

    @if ($result)
        <div @class([
            'space-y-4 rounded-2xl border-2 p-6 text-center',
            'border-emerald-500 bg-emerald-500/10' => $result === 'ok',
            'border-amber-500 bg-amber-500/10' => $result === 'already_used',
            'border-red-500 bg-red-500/10' => $result === 'expired' || $result === 'invalid',
        ])>
            @if ($result === 'ok')
                <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-emerald-500 text-white">
                    <flux:icon.check class="size-8" />
                </div>
                <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">Checked In</div>
            @elseif ($result === 'already_used')
                <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-amber-500 text-white">
                    <flux:icon.minus-circle class="size-8" />
                </div>
                <div class="text-2xl font-bold text-amber-600 dark:text-amber-400">Already Used</div>
                @if ($usedAt)
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Checked in at {{ $usedAt }}</p>
                @endif
            @elseif ($result === 'expired')
                <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-red-500 text-white">
                    <flux:icon.x-circle class="size-8" />
                </div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">Ticket Expired</div>
            @else
                <div class="mx-auto flex size-16 items-center justify-center rounded-full bg-red-500 text-white">
                    <flux:icon.x-circle class="size-8" />
                </div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">Invalid Ticket</div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">This QR code is not recognized.</p>
            @endif

            @if ($ticketTypeName)
                <div class="text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ $ticketTypeName }}</div>
            @endif
            @if ($eventName)
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $eventName }}</div>
            @endif

            <flux:button wire:click="resetScan" variant="primary" class="w-full">Scan Next Ticket</flux:button>
        </div>
    @endif

    @if (! $result && $eventId)
        <div class="border-t border-zinc-200 pt-4 dark:border-zinc-700">
            <label for="manual-token" class="mb-1 block text-sm font-medium text-zinc-500 dark:text-zinc-400">Or enter a token manually</label>
            <div class="flex gap-2">
                <input wire:model="manualToken" id="manual-token" type="text" placeholder="Paste ticket token or verify URL" class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:outline-none focus:ring-2 focus:ring-zinc-400 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                <flux:button wire:click="manualCheckIn" variant="filled">Check in</flux:button>
            </div>
        </div>
    @endif
</div>

@script
    <script>
        let scanner = null;

        async function startScanner() {
            const el = document.getElementById('qr-reader');
            if (!el || scanner) return;

            scanner = new Html5Qrcode('qr-reader');

            try {
                await scanner.start(
                    { facingMode: 'environment' },
                    { fps: 10, qrbox: { width: 250, height: 250 } },
                    async (decodedText) => {
                        await stopScanner();
                        $wire.checkIn(decodedText);
                    },
                    () => {}
                );
            } catch (error) {
                scanner = null;
            }
        }

        async function stopScanner() {
            if (!scanner) return;
            await scanner.stop();
            scanner.clear();
            scanner = null;
        }

        function boot() {
            if (typeof Html5Qrcode === 'undefined') {
                setTimeout(boot, 100);
                return;
            }

            startScanner();
        }

        $wire.$on('scanner-reset', () => {
            setTimeout(startScanner, 150);
        });

        boot();
    </script>
@endscript
