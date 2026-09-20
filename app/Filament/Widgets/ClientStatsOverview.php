<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\GroupCounsellingLog;
use App\Models\IndividualCounsellingLog;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ClientStatsOverview extends StatsOverviewWidget
{
    private const TARGET_INDIVIDUAL_HOURS = 70;

    private const TARGET_GROUP_HOURS = 50;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        return [
            Stat::make('Clients', Client::count()),
            $this->individualHoursStat(),
            $this->groupHoursStat(),
        ];
    }

    private function individualHoursStat(): Stat
    {
        return $this->hoursStat('Individual counselling hours', IndividualCounsellingLog::query(), self::TARGET_INDIVIDUAL_HOURS);
    }

    private function groupHoursStat(): Stat
    {
        return $this->hoursStat('Group counselling hours', GroupCounsellingLog::query(), self::TARGET_GROUP_HOURS);
    }

    /**
     * @param  Builder<Model>  $logs
     */
    private function hoursStat(string $label, Builder $logs, int $target): Stat
    {
        $hoursPerSession = $logs
            ->whereNotNull('duration_hours')
            ->orderBy('session_date')
            ->orderBy('id')
            ->pluck('duration_hours')
            ->map(fn (mixed $hours): float => (float) $hours);

        $total = $hoursPerSession->sum();

        $runningTotal = 0.0;
        $cumulative = $hoursPerSession
            ->map(function (float $hours) use (&$runningTotal): float {
                return $runningTotal += $hours;
            })
            ->all();

        $formattedTotal = rtrim(rtrim(number_format($total, 2), '0'), '.');

        $remaining = max($target - $total, 0);
        $percent = min(round($total / $target * 100), 100);

        return Stat::make($label, "{$formattedTotal} / {$target}")
            ->description($remaining > 0
                ? rtrim(rtrim(number_format($remaining, 2), '0'), '.')." h to go ({$percent}%)"
                : 'Target reached')
            ->descriptionIcon($remaining > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
            ->color($remaining > 0 ? 'primary' : 'success')
            ->chart($cumulative);
    }
}
