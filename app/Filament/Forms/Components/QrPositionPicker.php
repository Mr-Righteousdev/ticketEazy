<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Storage;

class QrPositionPicker extends Component
{
    protected string $view = 'filament.forms.components.qr-position-picker';

    public static function make(): static
    {
        return app(static::class);
    }

    protected string|Closure|null $previewUrl = null;

    protected array|Closure $pdfDimensions = ['width' => 0, 'height' => 0];

    public function previewUrl(string|Closure|null $url): static
    {
        $this->previewUrl = $url;

        return $this;
    }

    public function pdfDimensions(array|Closure $dimensions): static
    {
        $this->pdfDimensions = $dimensions;

        return $this;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->evaluate($this->previewUrl);
    }

    public function getPdfWidth(): float
    {
        $dimensions = $this->evaluate($this->pdfDimensions);

        return (float) ($dimensions['width'] ?? 0);
    }

    public function getPdfHeight(): float
    {
        $dimensions = $this->evaluate($this->pdfDimensions);

        return (float) ($dimensions['height'] ?? 0);
    }

    public function getPreviewNaturalWidth(): int
    {
        $url = $this->getPreviewUrl();
        if (! $url) {
            return 800;
        }

        $path = Storage::disk('local')->path($url);
        if (! file_exists($path)) {
            return 800;
        }

        $size = @getimagesize($path);

        return $size ? $size[0] : 800;
    }

    public function getPreviewNaturalHeight(): int
    {
        $url = $this->getPreviewUrl();
        if (! $url) {
            return 600;
        }

        $path = Storage::disk('local')->path($url);
        if (! file_exists($path)) {
            return 600;
        }

        $size = @getimagesize($path);

        return $size ? $size[1] : 600;
    }
}
