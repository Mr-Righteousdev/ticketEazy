<?php

namespace App\Filament\Resources\EventOperatorAssignments;

use App\Filament\Resources\EventOperatorAssignments\Pages\CreateEventOperatorAssignment;
use App\Filament\Resources\EventOperatorAssignments\Pages\EditEventOperatorAssignment;
use App\Filament\Resources\EventOperatorAssignments\Pages\ListEventOperatorAssignments;
use App\Filament\Resources\EventOperatorAssignments\Schemas\EventOperatorAssignmentForm;
use App\Filament\Resources\EventOperatorAssignments\Tables\EventOperatorAssignmentsTable;
use App\Models\EventOperatorAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventOperatorAssignmentResource extends Resource
{
    protected static ?string $model = EventOperatorAssignment::class;

    protected static ?string $navigationLabel = 'Gate Assignments';

    protected static ?string $singularModelLabel = 'Gate Assignment';

    protected static ?string $pluralModelLabel = 'Gate Assignments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return EventOperatorAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventOperatorAssignmentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEventOperatorAssignments::route('/'),
            'create' => CreateEventOperatorAssignment::route('/create'),
            'edit' => EditEventOperatorAssignment::route('/{record}/edit'),
        ];
    }
}
