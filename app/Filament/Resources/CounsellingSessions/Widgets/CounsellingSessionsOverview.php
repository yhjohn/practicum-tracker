<?php

namespace App\Filament\Resources\CounsellingSessions\Widgets;

use App\Enums\SessionType;
use App\Models\CounsellingSession;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class CounsellingSessionsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalMinutes = CounsellingSession::sum('duration_minutes');

        $weekMinutes = CounsellingSession::whereBetween('session_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])->sum('duration_minutes');

        $groupMinutes = CounsellingSession::where('type', SessionType::Group)
            ->sum('duration_minutes');

        $sessionCount = CounsellingSession::count();

        return [
            Stat::make('Total Hours', $this->formatHours($totalMinutes)),
            Stat::make('Hours This Week', $this->formatHours($weekMinutes)),
            Stat::make('Group Hours', $this->formatHours($groupMinutes)),
            Stat::make('# of Sessions', $sessionCount),
        ];
    }

    private function formatHours(?int $minutes): string
    {
        return number_format(($minutes ?? 0) / 60, 1).'h';
    }
}
