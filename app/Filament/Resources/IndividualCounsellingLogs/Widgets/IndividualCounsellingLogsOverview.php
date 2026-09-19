<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Widgets;

use App\Models\IndividualCounsellingLog;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class IndividualCounsellingLogsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalHours = IndividualCounsellingLog::sum('duration_hours');

        $weekHours = IndividualCounsellingLog::whereBetween('session_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->sum('duration_hours');

        $sessionCount = IndividualCounsellingLog::count();

        return [
            Stat::make('Total Hours', $this->formatHours($totalHours)),
            Stat::make('Hours This Week', $this->formatHours($weekHours)),
            Stat::make('# of Sessions', $sessionCount),
        ];
    }

    private function formatHours(float|int|string|null $hours): string
    {
        return number_format((float) $hours, 1).'h';
    }
}
