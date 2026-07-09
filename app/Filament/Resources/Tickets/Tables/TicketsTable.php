<?php

namespace App\Filament\Resources\Tickets\Tables;

use App\Filament\Resources\Tickets\TicketResource;
use App\Models\Ticket;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
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
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->url(fn (Ticket $record): string => TicketResource::getUrl('edit', ['record' => $record])),
                \Filament\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading(fn (Ticket $record): string => "Ticket #{$record->id}")
                    ->modalContent(function (Ticket $record): HtmlString {
                        $qrOptions = new QROptions([
                            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                            'imageBase64' => true,
                            'scale' => 10,
                        ]);
                        $qrCode = new QRCode($qrOptions);
                        $qrDataUri = $qrCode->render($record->token);

                        $html = view('filament.ticket-preview', [
                            'ticket' => $record,
                            'qrDataUri' => $qrDataUri,
                        ])->render();

                        return new HtmlString($html);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                \Filament\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(function (Ticket $record) {
                        $shortToken = substr($record->token, 0, 12);
                        $eventId = $record->ticketType->event_id;
                        $typeId = $record->ticket_type_id;

                        $pattern = Storage::disk('local')->path(
                            "tickets/{$eventId}/{$typeId}/batch-*/ticket-{$shortToken}.pdf"
                        );
                        $matches = glob($pattern);

                        if (empty($matches)) {
                            Notification::make()
                                ->title('PDF not found')
                                ->danger()
                                ->send();
                            return;
                        }

                        return response()->download($matches[0], "ticket-{$shortToken}.pdf");
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('downloadPdfs')
                        ->label('Download PDFs')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->form([
                            TextInput::make('filename')
                                ->label('ZIP filename')
                                ->placeholder('tickets')
                                ->default('tickets')
                                ->required()
                                ->alphaDash(),
                        ])
                        ->action(function (array $data, iterable $records) {
                            $zipPath = tempnam(sys_get_temp_dir(), 'tickets_').'.zip';
                            $zip = new ZipArchive;

                            if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
                                return;
                            }

                            $added = 0;

                            foreach ($records as $ticket) {
                                $shortToken = substr($ticket->token, 0, 12);
                                $eventId = $ticket->ticketType->event_id;
                                $typeId = $ticket->ticket_type_id;

                                $pattern = Storage::disk('local')->path(
                                    "tickets/{$eventId}/{$typeId}/batch-*/ticket-{$shortToken}.pdf"
                                );
                                $matches = glob($pattern);

                                if (! empty($matches)) {
                                    $zip->addFile($matches[0], "ticket-{$shortToken}.pdf");
                                    $added++;
                                }
                            }

                            $zip->close();

                            if ($added === 0) {
                                unlink($zipPath);
                                Notification::make()
                                    ->title('No PDFs found for selected tickets')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $filename = $data['filename'] ?? 'tickets';

                            return response()->download($zipPath, "{$filename}.zip")->deleteFileAfterSend(true);
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
