<?php

namespace App\Filament\Widgets;

use App\Models\ScanLog;
use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AttendanceStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $since = Carbon::now()->subDays(30);

        $issued = Ticket::where('created_at', '>=', $since)->count();

        $checkedIn = ScanLog::where('scanned_at', '>=', $since)
            ->where('result', 'ok')
            ->count();

        $remaining = Ticket::where('created_at', '>=', $since)
            ->whereIn('status', ['generated', 'sent'])
            ->count();

        $rejected = ScanLog::where('scanned_at', '>=', $since)
            ->whereIn('result', ['invalid', 'expired'])
            ->count();

        return [
            Stat::make('Tickets Issued', number_format($issued))
                ->description('Last 30 days')
                ->descriptionIcon('heroicon-o-document-arrow-down')
                ->color('primary'),
            Stat::make('Checked In', number_format($checkedIn))
                ->description('Successful scans · 30d')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Remaining', number_format($remaining))
                ->description('Not yet scanned · 30d')
                ->descriptionIcon('heroicon-o-ticket')
                ->color('warning'),
            Stat::make('Rejected Scans', number_format($rejected))
                ->description('Invalid / expired · 30d')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
