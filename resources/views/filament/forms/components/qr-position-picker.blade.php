@php
    $previewUrl = $getPreviewUrl();
    $pdfWidth = $getPdfWidth();
    $pdfHeight = $getPdfHeight();

    $qrXComponent = $getContainer()->getComponent(fn ($component) => $component instanceof \Filament\Forms\Components\Field && $component->getName() === 'qr_x');
    $qrYComponent = $getContainer()->getComponent(fn ($component) => $component instanceof \Filament\Forms\Components\Field && $component->getName() === 'qr_y');
    $qrSizeComponent = $getContainer()->getComponent(fn ($component) => $component instanceof \Filament\Forms\Components\Field && $component->getName() === 'qr_size');

    $qrXState = $qrXComponent?->getState();
    $qrYState = $qrYComponent?->getState();

    $initialX = (float) ($qrXState ?? 10);
    $initialY = (float) ($qrYState ?? 10);
    $initialSize = (float) ($qrSizeComponent?->getState() ?? 25);
@endphp

<div
    wire:key="qr-picker-{{ md5($previewUrl ?? '') }}"
    x-data="{
        x: {{ $initialX }},
        y: {{ $initialY }},
        qrSize: {{ $initialSize }},
        pdfWidth: {{ $pdfWidth }},
        pdfHeight: {{ $pdfHeight }},
        placed: true,
        flash: false,

        get sx() {
            const w = this.$refs.previewImg?.offsetWidth || 1;
            return this.pdfWidth / w;
        },

        get sy() {
            const h = this.$refs.previewImg?.offsetHeight || 1;
            return this.pdfHeight / h;
        },

        get boxStyle() {
            const sx = this.sx;
            const sy = this.sy;

            if (! isFinite(sx) || sx <= 0 || ! isFinite(sy) || sy <= 0) {
                return { display: 'none' };
            }

            const px = this.x / sx;
            const py = this.y / sy;
            const pw = this.qrSize / sx;
            const ph = this.qrSize / sy;
            return {
                left: px + 'px',
                top: py + 'px',
                width: Math.max(pw, 8) + 'px',
                height: Math.max(ph, 8) + 'px',
            };
        },

        placeQr(event) {
            const rect = this.$refs.previewImg.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const clickY = event.clientY - rect.top;
            this.x = Math.round(clickX * this.sx * 10) / 10;
            this.y = Math.round(clickY * this.sy * 10) / 10;
            this.placed = true;
            this.syncToLivewire();
            this.flash = true;
            setTimeout(() => this.flash = false, 2000);
        },

        syncToLivewire() {
            $wire.set('data.qr_x', this.x);
            $wire.set('data.qr_y', this.y);
            $wire.set('data.qr_size', this.qrSize);
        },
    }"
    class="space-y-4"
>
    @if ($previewUrl)
        <div class="border bg-gray-100 rounded-lg overflow-hidden text-center p-4">
            <div style="position: relative; display: inline-block; cursor: crosshair; max-width: 100%;">
                <img
                    x-ref="previewImg"
                    src="{{ $previewUrl }}"
                    style="display: block; max-width: 100%; height: auto;"
                    draggable="false"
                    @click="placeQr($event)"
                >
                <div
                    :class="{ 'opacity-0': !placed }"
                    :style="boxStyle"
                    style="position: absolute; pointer-events: none; border: 2px solid #22c55e; background: rgba(34, 197, 94, 0.15); transition: opacity 0.2s; box-sizing: border-box;"
                ></div>
            </div>
        </div>
    @else
        <div class="border rounded-lg bg-gray-50 p-8 text-center text-gray-400 text-sm">
            Upload a PDF template to preview and position the QR code.
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
        <span x-text="'X: ' + Number(x).toFixed(1) + ' mm'"></span>
        <span x-text="'Y: ' + Number(y).toFixed(1) + ' mm'"></span>
        <div class="flex items-center gap-2">
            <label class="text-gray-600">QR Size:</label>
            <input type="range" x-model="qrSize" min="10" max="80" step="1" @input="syncToLivewire()" class="w-32 accent-emerald-500">
            <span x-text="Number(qrSize).toFixed(0) + ' mm'" class="w-14"></span>
        </div>
    </div>

    <div
        x-show="flash"
        x-transition.opacity.duration.300ms
        class="text-sm text-emerald-600 font-medium text-center"
    >
        QR position saved
    </div>
</div>
