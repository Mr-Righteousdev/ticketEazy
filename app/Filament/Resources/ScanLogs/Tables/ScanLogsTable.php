<?php

namespace App\Filament\Resources\ScanLogs\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ScanLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('scanned_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('ticket.ticketType.event.name')
                    ->label('Event')
                    ->searchable(),
                TextColumn::make('ticket.ticketType.name')
                    ->label('Ticket Type')
                    ->searchable(),
                TextColumn::make('ticket.short_code')
                    ->label('Ticket Code')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Code copied'),
                TextColumn::make('scannedBy.name')
                    ->label('Operator')
                    ->searchable(),
                TextColumn::make('result')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'ok' => 'Checked In',
                        'already_used' => 'Already Used',
                        'expired' => 'Expired',
                        'invalid' => 'Invalid',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'ok' => 'success',
                        'already_used' => 'warning',
                        'expired' => 'danger',
                        'invalid' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Event')
                    ->relationship('ticket.ticketType.event', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('operator')
                    ->label('Operator')
                    ->relationship('scannedBy', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('result')
                    ->label('Result')
                    ->options([
                        'ok' => 'Checked In',
                        'already_used' => 'Already Used',
                        'expired' => 'Expired',
                        'invalid' => 'Invalid',
                    ])
                    ->attribute('result'),
                Filter::make('scanned_at')
                    ->label('Date Range')
                    ->form([
                        DatePicker::make('from')
                            ->label('From'),
                        DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, string $date): Builder => $query->whereDate('scanned_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, string $date): Builder => $query->whereDate('scanned_at', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('scanned_at', 'desc');
    }
}
