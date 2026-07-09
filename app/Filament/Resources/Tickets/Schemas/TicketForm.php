<?php

namespace App\Filament\Resources\Tickets\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('ticket_type_id')
                    ->relationship('ticketType', 'name')
                    ->required(),
                TextInput::make('token')
                    ->required(),
                Select::make('status')
                    ->options([
                        'generated' => 'Generated',
                        'sent' => 'Sent',
                        'used' => 'Used',
                        'expired' => 'Expired',
                    ])
                    ->required(),
            ]);
    }
}
