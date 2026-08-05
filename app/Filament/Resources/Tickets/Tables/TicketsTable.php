<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Filament\Resources\Tickets\TicketResource;
use App\Models\Ticket;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use ZipArchive;

class TicketsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ticketType.event.name')
                    ->label('Event')
                    ->searchable(),
                TextColumn::make('ticketType.name')
                    ->label('Ticket Type')
                    ->searchable(),
                TextColumn::make('batch_path')
                    ->label('Batch')
                    ->formatStateUsing(
                        fn (?string $state): ?string => $state ? self::batchLabel($state) : null
                    )
                    ->action(function (Ticket $record) {
                        $relative = self::batchZipRelative($record);

                        if (! $relative) {
                            Notification::make()
                                ->title('No batch ZIP available')
                                ->danger()
                                ->send();

                            return;
                        }

                        $path = Storage::disk('local')->path($relative);

                        if (! file_exists($path)) {
                            Notification::make()
                                ->title('Batch ZIP not found')
                                ->body('The ZIP for this batch is missing on disk.')
                                ->danger()
                                ->send();

                            return;
                        }

                        return response()->download($path, basename($path));
                    })
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('token')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Token copied')
                    ->limit(20),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'generated' => 'gray',
                        'sent' => 'info',
                        'used' => 'success',
                        'expired' => 'danger',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('used_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Event')
                    ->relationship('ticketType.event', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('ticketType')
                    ->label('Ticket Type')
                    ->relationship('ticketType', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('batch')
                    ->label('Batch')
                    ->options(self::batchOptions())
                    ->attribute('batch_path'),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'generated' => 'Generated',
                        'sent' => 'Sent',
                        'used' => 'Used',
                        'expired' => 'Expired',
                        'failed' => 'Failed',
                    ])
                    ->attribute('status'),
            ])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Ticket $record): string => TicketResource::getUrl('edit', ['record' => $record])),
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (Ticket $record): string => "Ticket #{$record->id}")
                    ->modalWidth('4xl')
                    ->modalContent(function (Ticket $record): HtmlString {
                        $qrOptions = new QROptions([
                            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                            'imageBase64' => true,
                            'scale' => 12,
                        ]);
                        $qrCode = new QRCode($qrOptions);
                        $qrDataUri = $qrCode->render(route('ticket.verify', $record->token));

                        $html = view('filament.ticket-preview', [
                            'ticket' => $record,
                            'qrDataUri' => $qrDataUri,
                        ])->render();

                        return new HtmlString($html);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function (Ticket $record) {
                        $path = self::individualPdfPath($record);

                        if (! $path) {
                            Notification::make()
                                ->title('PDF not found')
                                ->danger()
                                ->send();

                            return;
                        }

                        return response()->download($path, "ticket-{$record->id}.pdf");
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('downloadBatches')
                        ->label('Download Batch(es)')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (iterable $records) {
                            $zipPaths = [];

                            foreach ($records as $ticket) {
                                $relative = self::batchZipRelative($ticket);

                                if (! $relative) {
                                    continue;
                                }

                                $path = Storage::disk('local')->path($relative);

                                if (file_exists($path)) {
                                    $zipPaths[$relative] = $path;
                                }
                            }

                            if (empty($zipPaths)) {
                                Notification::make()
                                    ->title('No batch ZIPs found for selected tickets')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            if (count($zipPaths) === 1) {
                                $path = reset($zipPaths);

                                return response()->download($path, basename($path));
                            }

                            $combinedPath = tempnam(sys_get_temp_dir(), 'batches_').'.zip';
                            $zip = new ZipArchive;

                            if ($zip->open($combinedPath, ZipArchive::CREATE) === true) {
                                foreach ($zipPaths as $path) {
                                    $zip->addFile($path, basename($path));
                                }
                                $zip->close();
                            }

                            return response()->download($combinedPath, 'ticket-batches.zip')->deleteFileAfterSend(true);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function individualPdfPath(Ticket $ticket): ?string
    {
        if (! $ticket->batch_path) {
            return null;
        }

        $path = Storage::disk('local')->path("{$ticket->batch_path}/ticket-{$ticket->id}.pdf");

        return file_exists($path) ? $path : null;
    }

    private static function batchZipRelative(Ticket $ticket): ?string
    {
        if (! $ticket->batch_path) {
            return null;
        }

        return dirname($ticket->batch_path)."/downloads/{$ticket->ticket_type_id}-".basename($ticket->batch_path).'.zip';
    }

    private static function batchOptions(): array
    {
        $paths = Ticket::query()
            ->whereNotNull('batch_path')
            ->distinct()
            ->orderByDesc('batch_path')
            ->pluck('batch_path');

        return $paths
            ->mapWithKeys(fn (string $path): array => [$path => self::batchLabel($path)])
            ->all();
    }

    private static function batchLabel(string $path): string
    {
        $timestamp = (int) Str::after($path, 'batch-');

        if (! $timestamp) {
            return Str::afterLast($path, '/');
        }

        return 'Batch · '.now()->setTimestamp($timestamp)->format('M j, Y g:i A');
    }
}
