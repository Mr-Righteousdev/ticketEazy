<?php

namespace App\Filament\Resources\EventOperatorAssignments\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class EventOperatorAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Event')
                    ->relationship('event', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn ($state, $set) => $set('user_id', null)),
                Select::make('user_id')
                    ->label('Operator')
                    ->relationship('user', 'name', fn ($query) => $query->whereHas('roles', fn ($q) => $q->where('name', 'operator')))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('ticket_type_id')
                    ->label('Ticket Type')
                    ->options(fn ($get) => \App\Models\TicketType::where('event_id', $get('event_id'))->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('gate_name')
                    ->label('Gate Name')
                    ->placeholder('e.g. Gate 1, VIP Entrance')
                    ->maxLength(255),
            ]);
    }
}
