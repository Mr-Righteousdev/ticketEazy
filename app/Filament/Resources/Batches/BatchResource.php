<?php

namespace App\Filament\Resources\Batches;

use App\Filament\Resources\Batches\Pages\ListBatches;
use App\Filament\Resources\Batches\Tables\BatchesTable;
use App\Models\Batch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BatchResource extends Resource
{
    protected static ?string $model = Batch::class;

    protected static ?string $navigationLabel = 'Batches';

    protected static ?string $singularModelLabel = 'Batch';

    protected static ?string $pluralModelLabel = 'Batches';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?int $navigationSort = 3;

    public static function table(Table $table): Table
    {
        return BatchesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBatches::route('/'),
        ];
    }
}
