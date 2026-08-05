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

                    $path = $ticket->batch_path
                        ? Storage::disk('local')->path("{$ticket->batch_path}/ticket-{$ticket->id}.pdf")
                        : null;

                    if (! $path || ! file_exists($path)) {
                        Notification::make()
                            ->title('PDF not found')
                            ->body('The ticket PDF file could not be located in storage.')
                            ->danger()
                            ->send();

                        return;
                    }

                    return response()->download($path, "ticket-{$ticket->id}.pdf");
                }),
            DeleteAction::make(),
        ];
    }
}
