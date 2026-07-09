<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                DatePicker::make('date')
                    ->required(),
                TimePicker::make('time')
                    ->required(),
                TextInput::make('venue')
                    ->required(),
                TextInput::make('capacity')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'ended' => 'Ended',
                    ])
                    ->required()
                    ->default('draft'),
            ]);
    }
}
