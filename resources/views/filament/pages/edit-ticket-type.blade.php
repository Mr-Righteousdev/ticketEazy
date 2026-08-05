<x-filament-panels::page>
    @if ($this->pendingBatchZip)
        <div wire:poll.5s="checkBatchReady" class="hidden"></div>
    @endif

    {{ $this->content }}
</x-filament-panels::page>
