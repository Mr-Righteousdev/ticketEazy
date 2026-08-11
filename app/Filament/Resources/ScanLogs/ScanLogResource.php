<?php

namespace App\Filament\Resources\ScanLogs;

use App\Filament\Resources\ScanLogs\Pages\ListScanLogs;
use App\Filament\Resources\ScanLogs\Tables\ScanLogsTable;
use App\Models\ScanLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ScanLogResource extends Resource
{
    protected static ?string $model = ScanLog::class;

    protected static ?string $navigationLabel = 'Scan Logs';

    protected static ?string $pluralModelLabel = 'Scan Logs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return ScanLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListScanLogs::route('/'),
        ];
    }
}
