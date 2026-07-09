@php
    $previewUrl = $getPreviewUrl();
    $pdfWidth = $getPdfWidth();
    $pdfHeight = $getPdfHeight();
    $imgNaturalWidth = $getPreviewNaturalWidth();
    $imgNaturalHeight = $getPreviewNaturalHeight();

    $initialX = (float) ($record?->qr_x ?? 10);
    $initialY = (float) ($record?->qr_y ?? 10);
    $initialSize = (float) ($record?->qr_size ?? 12);
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
        flash: false,

        get sx() {
            return this.imgW > 0 ? this.pdfWidth / this.imgW : 1;
        },

        get sy() {
            return this.imgH > 0 ? this.pdfHeight / this.imgH : 1;
        },

        placeQr(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const clickY = event.clientY - rect.top;

            this.x = Math.round(clickX * this.sx * 10) / 10;
            this.y = Math.round(clickY * this.sy * 10) / 10;
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
        <div class="border bg-gray-100" style="width: 100%; min-height: 200px; border-radius: 8px; overflow: hidden; text-align: center; padding: 16px; cursor: crosshair;">
            <img
                x-ref="previewImg"
                src="{{ $previewUrl }}"
                style="display: inline-block;"
                draggable="false"
                @click="placeQr($event)"
                @@error="console.log('Preview load error', $event)"
            >
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
