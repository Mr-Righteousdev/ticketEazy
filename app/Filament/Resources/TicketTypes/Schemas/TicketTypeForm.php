<?php

namespace App\Filament\Resources\TicketTypes\Schemas;

use App\Actions\GeneratePdfPreview;
use App\Filament\Forms\Components\QrPositionPicker;
use App\Models\TicketType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TicketTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->relationship('event', 'name')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('price')
                    ->numeric()
                    ->default(null)
                    ->prefix('$'),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                FileUpload::make('template_path')
                    ->label('PDF Template')
                    ->directory('templates')
                    ->acceptedFileTypes(['application/pdf'])
                    ->live(),
                Toggle::make('is_discount')
                    ->label('Discount ticket?')
                    ->inline(false),
                TextInput::make('discount_label')
                    ->label('Discount label'),
                Select::make('parent_type_id')
                    ->relationship('parentType', 'name')
                    ->label('Parent ticket type'),
                Section::make('QR Code Position')
                    ->description('Click on the preview to place the QR code, then drag or resize.')
                    ->schema([
                        Hidden::make('qr_x'),
                        Hidden::make('qr_y'),
                        Hidden::make('qr_size'),
                        QrPositionPicker::make()
                            ->columnSpanFull()
                            ->previewUrl(function (?TicketType $record, $get): ?string {
                                $path = $get('template_path') ?? $record?->template_path;

                                return $path && app(GeneratePdfPreview::class)->generate($path)
                                    ? route('previews.show', ['hash' => md5($path)])
                                    : null;
                            })
                            ->pdfDimensions(function (?TicketType $record, $get): array {
                                $path = $get('template_path') ?? $record?->template_path;

                                return $path
                                    ? (app(GeneratePdfPreview::class)->getPageDimensions($path) ?? ['width' => 0, 'height' => 0])
                                    : ['width' => 0, 'height' => 0];
                            }),
                    ]),
            ]);
    }
}
