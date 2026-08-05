<?php

namespace App\Filament\Resources\TicketTypes\Pages;

use App\Filament\Resources\TicketTypes\TicketTypeResource;
use App\Jobs\GenerateTickets;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditTicketType extends EditRecord
{
    protected static string $resource = TicketTypeResource::class;

    protected string $view = 'filament.pages.edit-ticket-type';

    public ?string $pendingBatchZip = null;

    public ?int $pendingBatchTimestamp = null;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateTickets')
                ->label('Generate Tickets')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->form([
                    TextInput::make('quantity')
                        ->label('Number of tickets')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->maxValue($this->record->quantity),
                ])
                ->action(function (array $data): void {
                    $quantity = (int) $data['quantity'];
                    $timestamp = now()->timestamp;

                    $this->pendingBatchZip = "tickets/{$this->record->event_id}/{$this->record->id}/downloads/{$this->record->id}-batch-{$timestamp}.zip";
                    $this->pendingBatchTimestamp = $timestamp;

                    dispatch(new GenerateTickets($this->record, $quantity, $timestamp));

                    Notification::make()
                        ->title('Tickets queued for generation')
                        ->body("{$quantity} tickets are being generated. You will be notified when the batch is ready to download.")
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }

    public function checkBatchReady(): void
    {
        if (! $this->pendingBatchZip || ! $this->pendingBatchTimestamp) {
            return;
        }

        if (Storage::disk('local')->exists($this->pendingBatchZip)) {
            Notification::make()
                ->title('Tickets ready to download')
                ->body('Your ticket batch has been generated and is ready to download.')
                ->success()
                ->actions([
                    Action::make('downloadBatch')
                        ->label('Download batch ZIP')
                        ->url(route('tickets.batch.download', [
                            'typeId' => $this->record->id,
                            'timestamp' => $this->pendingBatchTimestamp,
                        ]))
                        ->openUrlInNewTab(),
                ])
                ->send();

            $this->pendingBatchZip = null;
            $this->pendingBatchTimestamp = null;

            return;
        }

        if (time() - $this->pendingBatchTimestamp > 600) {
            Notification::make()
                ->title('Batch generation may have failed')
                ->body('The batch ZIP was not created within 10 minutes. Check the tickets list and logs.')
                ->warning()
                ->send();

            $this->pendingBatchZip = null;
            $this->pendingBatchTimestamp = null;
        }
    }
}
