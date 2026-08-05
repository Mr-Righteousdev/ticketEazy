@php
    $previewUrl = $getPreviewUrl();
    $pdfWidth = $getPdfWidth();
    $pdfHeight = $getPdfHeight();
    $imgNaturalWidth = $getPreviewNaturalWidth();
    $imgNaturalHeight = $getPreviewNaturalHeight();

    $initialX = (float) ($record?->qr_x ?? 10);
    $initialY = (float) ($record?->qr_y ?? 10);
    $initialSize = (float) ($record?->qr_size ?? 25);
    $hasPosition = $initialX > 0 || $initialY > 0;
@endphp

<div
    x-data="{
        x: {{ $initialX }},
        y: {{ $initialY }},
        qrSize: {{ $initialSize }},
        pdfWidth: {{ $pdfWidth }},
        pdfHeight: {{ $pdfHeight }},
        imgW: {{ $imgNaturalWidth }},
        imgH: {{ $imgNaturalHeight }},
        placed: {{ $hasPosition ? 'true' : 'false' }},
        flash: false,

        get sx() {
            return this.imgW > 0 ? this.pdfWidth / this.imgW : 1;
        },

        get sy() {
            return this.imgH > 0 ? this.pdfHeight / this.imgH : 1;
        },

        get boxStyle() {
            const w = Math.max(this.qrSize / this.sx, 10);
            const h = Math.max(this.qrSize / this.sy, 10);
            return {
                left: (this.x / this.sx) + 'px',
                top: (this.y / this.sy) + 'px',
                width: w + 'px',
                height: h + 'px',
            };
        },

        placeQr(event) {
            const rect = event.currentTarget.getBoundingClientRect();
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
        <div class="border bg-gray-100" style="width: 100%; min-height: 200px; border-radius: 8px; overflow: hidden; text-align: center; padding: 16px;">
            <div style="position: relative; display: inline-block; cursor: crosshair;">
                <img
                    x-ref="previewImg"
                    src="{{ $previewUrl }}"
                    style="display: block;"
                    draggable="false"
                    @click="placeQr($event)"
                    @@error="console.log('Preview load error', $event)"
                >

                <div
                    :class="{ 'opacity-0': !placed }"
                    :style="boxStyle"
                    style="position: absolute; pointer-events: none; border: 2px solid #22c55e; background: rgba(34, 197, 94, 0.15); transition: opacity 0.2s;"
                ></div>
            </div>
        </div>
    @else
        <div class="border rounded-lg bg-gray-50 p-8 text-center text-gray-400 text-sm">
            Upload a PDF template to preview and position the QR code.
        </div>
    @endif

    <div class="flex items-center gap-4 text-sm text-gray-600">
        <span x-text="'X: ' + Number(x).toFixed(1) + ' mm'"></span>
        <span x-text="'Y: ' + Number(y).toFixed(1) + ' mm'"></span>
        <span x-text="'Size: ' + Number(qrSize).toFixed(1) + ' mm'"></span>
    </div>

    <div
        x-show="flash"
        x-transition.opacity.duration.300ms
        class="text-sm text-emerald-600 font-medium text-center"
    >
        QR position saved
    </div>
</div>
