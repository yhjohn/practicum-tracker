<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ClientStatsOverview extends StatsOverviewWidget
{
    private const TARGET_INDIVIDUAL_HOURS = 70;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Clients', Client::count()),
            $this->individualHoursStat(),
        ];
    }

    private function individualHoursStat(): Stat
    {
        $hoursPerSession = IndividualCounsellingLog::query()
            ->whereNotNull('duration_hours')
            ->orderBy('session_date')
            ->orderBy('id')
            ->pluck('duration_hours')
            ->map(fn (mixed $hours): float => (float) $hours);

        $total = $hoursPerSession->sum();
        $remaining = max(self::TARGET_INDIVIDUAL_HOURS - $total, 0);
        $percent = min(round($total / self::TARGET_INDIVIDUAL_HOURS * 100), 100);

        $runningTotal = 0.0;
        $cumulative = $hoursPerSession
            ->map(function (float $hours) use (&$runningTotal): float {
                return $runningTotal += $hours;
            })
            ->all();

        return Stat::make('Individual counselling hours', rtrim(rtrim(number_format($total, 2), '0'), '.').' / '.self::TARGET_INDIVIDUAL_HOURS)
            ->description($remaining > 0
                ? rtrim(rtrim(number_format($remaining, 2), '0'), '.')." h to go ({$percent}%)"
                : 'Target reached')
            ->descriptionIcon($remaining > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
            ->color($remaining > 0 ? 'primary' : 'success')
            ->chart($cumulative);
    }
}
