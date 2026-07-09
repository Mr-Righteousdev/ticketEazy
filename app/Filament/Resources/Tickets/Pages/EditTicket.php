<?php

namespace App\Filament\Resources\Tickets\Pages;

use App\Filament\Resources\Tickets\TicketResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditTicket extends EditRecord
{
    protected static string $resource = TicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    $ticket = $this->record;
                    $shortToken = substr($ticket->token, 0, 12);
                    $eventId = $ticket->ticketType->event_id;
                    $typeId = $ticket->ticket_type_id;

                    $pattern = Storage::disk('local')->path("tickets/{$eventId}/{$typeId}/batch-*/ticket-{$shortToken}.pdf");
                    $matches = glob($pattern);

                    if (empty($matches)) {
                        Notification::make()
                            ->title('PDF not found')
                            ->body('The ticket PDF file could not be located in storage.')
                            ->danger()
                            ->send();
                        return;
                    }

                    return response()->download($matches[0], "ticket-{$shortToken}.pdf");
                }),
            DeleteAction::make(),
        ];
    }
}
