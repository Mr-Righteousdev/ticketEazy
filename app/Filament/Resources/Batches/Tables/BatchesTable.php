<?php

namespace App\Filament\Resources\Batches\Tables;

use App\Actions\GeneratePrintSheet;
use App\Models\Batch;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class BatchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.name')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('ticketType.name')
                    ->label('Ticket Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('formatted_date')
                    ->label('Generated')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ticket_count')
                    ->label('Tickets')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'generating' => 'Generating',
                        'ready' => 'Ready',
                        'failed' => 'Failed',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'generating' => 'warning',
                        'ready' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('ticketType')
                    ->label('Ticket Type')
                    ->relationship('ticketType', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'generating' => 'Generating',
                        'ready' => 'Ready',
                        'failed' => 'Failed',
                    ])
                    ->attribute('status'),
            ])
            ->recordActions([
                Action::make('downloadZip')
                    ->label('Download ZIP')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Download Batch ZIP')
                    ->modalDescription('Download all individual ticket PDFs as a ZIP file.')
                    ->action(function (Batch $record) {
                        if ($record->status !== 'ready' || ! $record->zip_path) {
                            Notification::make()
                                ->title('Batch not ready')
                                ->body('This batch is not ready for download yet.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $path = Storage::disk('local')->path($record->zip_path);

                        if (! file_exists($path)) {
                            Notification::make()
                                ->title('ZIP file not found')
                                ->body('The ZIP file for this batch is missing from disk.')
                                ->danger()
                                ->send();

                            return;
                        }

                        return response()->download($path, basename($record->zip_path));
                    })
                    ->visible(fn (Batch $record): bool => $record->status === 'ready'),
                Action::make('downloadPrintSheet')
                    ->label('Download Print Sheet')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Download Print Sheet')
                    ->modalDescription('Download a single PDF with all tickets arranged on A4 pages, preserving original dimensions.')
                    ->action(function (Batch $record) {
                        if ($record->status !== 'ready') {
                            Notification::make()
                                ->title('Batch not ready')
                                ->body('This batch is not ready for download yet.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $pdfs = $record->individual_pdfs;

                        if (empty($pdfs)) {
                            Notification::make()
                                ->title('No PDFs found')
                                ->body('No individual ticket PDFs found for this batch.')
                                ->danger()
                                ->send();

                            return;
                        }

                        try {
                            $outputPath = app(GeneratePrintSheet::class)->handle($record);

                            $filename = "{$record->ticketType->name}-print-sheet-{$record->batch_timestamp}.pdf";

                            return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Print sheet generation failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (Batch $record): bool => $record->status === 'ready'),
            ])
            ->defaultSort('batch_timestamp', 'desc');
    }
}
