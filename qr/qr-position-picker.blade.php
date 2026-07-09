@php
    $previewUrl = $getPreviewUrl();
    $pdfWidth = $getPdfWidth();
    $pdfHeight = $getPdfHeight();

    $initialX = (float) ($record?->qr_x ?? 10);
    $initialY = (float) ($record?->qr_y ?? 10);
    $initialSize = (float) ($record?->qr_size ?? 30);
@endphp

<div
    x-data="{
        x: {{ $initialX }},
        y: {{ $initialY }},
        qrSize: {{ $initialSize }},
        pdfWidth: {{ $pdfWidth }},
        pdfHeight: {{ $pdfHeight }},
        displayWidth: 0,
        placed: {{ $initialX }} > 0 || {{ $initialY }} > 0,
        dragging: false,
        resizing: false,
        dragStartX: 0,
        dragStartY: 0,
        dragOrigX: 0,
        dragOrigY: 0,

        init() {
            this.$nextTick(() => this.measure());
        },

        measure() {
            if (this.$refs.previewImg) {
                this.displayWidth = this.$refs.previewImg.clientWidth;
            }
        },

        get scaleX() {
            return this.displayWidth > 0 ? this.pdfWidth / this.displayWidth : 1;
        },

        get scaleY() {
            return this.displayWidth > 0 ? this.pdfHeight / this.previewHeight : 1;
        },

        get previewHeight() {
            return this.displayWidth > 0 ? (this.displayWidth * this.pdfHeight / this.pdfWidth) : 400;
        },

        get qrStyle() {
            if (!this.displayWidth) return { display: 'none' };
            return {
                left: (this.x / this.scaleX) + 'px',
                top: (this.y / this.scaleY) + 'px',
                width: (this.qrSize / this.scaleX) + 'px',
                height: (this.qrSize / this.scaleX) + 'px',
            };
        },

        placeQr(event) {
            const rect = event.currentTarget.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const clickY = event.clientY - rect.top;

            this.x = Math.round(clickX * this.scaleX * 10) / 10;
            this.y = Math.round(clickY * this.scaleY * 10) / 10;
            this.placed = true;
            this.syncToLivewire();
        },

        startDrag(event) {
            this.dragging = true;
            this.dragStartX = event.clientX;
            this.dragStartY = event.clientY;
            this.dragOrigX = this.x;
            this.dragOrigY = this.y;

            const onMove = (e) => {
                if (!this.dragging) return;
                const dx = (e.clientX - this.dragStartX) * this.scaleX;
                const dy = (e.clientY - this.dragStartY) * this.scaleY;
                this.x = Math.max(0, Math.round((this.dragOrigX + dx) * 10) / 10);
                this.y = Math.max(0, Math.round((this.dragOrigY + dy) * 10) / 10);
            };

            const onUp = () => {
                this.dragging = false;
                this.syncToLivewire();
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        },

        startResize(event) {
            this.resizing = true;
            const startX = event.clientX;
            const startY = event.clientY;
            const origSize = this.qrSize;

            const onMove = (e) => {
                if (!this.resizing) return;
                const dx = (e.clientX - startX) * this.scaleX;
                const dy = (e.clientY - startY) * this.scaleY;
                const delta = Math.max(dx, dy);
                this.qrSize = Math.max(10, Math.round((origSize + delta) * 10) / 10);
            };

            const onUp = () => {
                this.resizing = false;
                this.syncToLivewire();
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
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
        <div class="relative border rounded-lg overflow-hidden bg-gray-100" style="cursor: crosshair;">
            <img
                x-ref="previewImg"
                src="{{ $previewUrl }}"
                class="block w-full h-auto select-none"
                draggable="false"
                @click="placeQr($event)"
                @load="measure()"
                @@error="console.log('Preview load error', $event)"
            >

            <div
                x-show="placed && displayWidth > 0"
                class="absolute border-2 border-blue-500 bg-blue-500/30 cursor-move flex items-center justify-center text-blue-700 text-xs font-bold select-none"
                :style="qrStyle"
                @mousedown.prevent="startDrag($event)"
            >
                QR
                <div
                    class="absolute -bottom-2.5 -right-2.5 w-5 h-5 bg-blue-500 border-2 border-white rounded-sm cursor-se-resize"
                    @mousedown.prevent="startResize($event)"
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
</div>
