<?php

namespace App\Filament\Resources\TicketTypes\Pages;

use App\Filament\Resources\TicketTypes\TicketTypeResource;
use App\Jobs\GenerateTickets;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditTicketType extends EditRecord
{
    protected static string $resource = TicketTypeResource::class;

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

                    dispatch(new GenerateTickets($this->record, $quantity));

                    Notification::make()
                        ->title('Tickets queued for generation')
                        ->body("{$quantity} tickets are being generated. Check the tickets list when complete.")
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
