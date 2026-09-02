<?php

namespace App\Filament\Resources\EventOperatorAssignments\Pages;

use App\Filament\Resources\EventOperatorAssignments\EventOperatorAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEventOperatorAssignments extends ListRecords
{
    protected static string $resource = EventOperatorAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modal(),
        ];
    }
}
