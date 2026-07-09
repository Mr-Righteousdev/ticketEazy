<?php

namespace App\Filament\Forms\Components;

use Closure;
use Filament\Schemas\Components\Component;

class QrPositionPicker extends Component
{
    protected string $view = 'filament.forms.components.qr-position-picker';

    public static function make(): static
    {
        return app(static::class);
    }

    protected string|Closure|null $previewUrl = null;

    protected array|Closure $pdfDimensions = ['width' => 0, 'height' => 0];

    protected int|Closure $displayWidth = 600;

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

    public function displayWidth(int|Closure $width): static
    {
        $this->displayWidth = $width;

        return $this;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->evaluate($this->previewUrl);
    }

    public function getDisplayWidth(): int
    {
        return (int) $this->evaluate($this->displayWidth);
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

    public function getDisplayHeight(): int
    {
        $dimensions = $this->evaluate($this->pdfDimensions);

        if (($dimensions['height'] ?? 0) && ($dimensions['width'] ?? 0)) {
            return (int) round($this->getDisplayWidth() * $this->getPdfHeight() / $this->getPdfWidth());
        }

        return 400;
    }
}
