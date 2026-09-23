<?php

namespace App\Models\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

trait HasWrittenAt
{
    /**
     * Sets written_at whenever the session date/time is filled in or changed,
     * on create and on update, unless written_at itself is being set.
     */
    protected static function bootHasWrittenAt(): void
    {
        static::saving(function (Model $log): void {
            if ($log->isDirty('written_at') || $log->session_date === null || $log->start_time === null) {
                return;
            }

            if ($log->written_at !== null && ! $log->isDirty(['session_date', 'start_time'])) {
                return;
            }

            $log->written_at = static::randomWrittenAt(Carbon::parse(
                $log->session_date->format('Y-m-d').' '.$log->start_time->format('H:i')
            ));
        });
    }

    /**
     * Pick a plausible "report written" moment: at least 3 hours after the
     * session started, within 24 hours of it, never between 12am–7am.
     */
    public static function randomWrittenAt(Carbon $sessionStart): Carbon
    {
        $lower = $sessionStart->copy()->addHours(3);
        $upper = $sessionStart->copy()->addHours(24);

        $ranges = [];
        $day = $lower->copy()->startOfDay();

        while ($day->lt($upper)) {
            $allowedStart = $day->copy()->addHours(7)->max($lower);
            $allowedEnd = $day->copy()->addDay()->min($upper);

            if ($allowedStart->lt($allowedEnd)) {
                $ranges[] = [$allowedStart, $allowedEnd];
            }

            $day->addDay();
        }

        $totalSeconds = array_sum(array_map(
            fn (array $range): int => $range[0]->diffInSeconds($range[1]),
            $ranges,
        ));

        $offset = random_int(0, max($totalSeconds - 1, 0));

        foreach ($ranges as [$start, $end]) {
            $length = $start->diffInSeconds($end);

            if ($offset < $length) {
                return $start->copy()->addSeconds($offset);
            }

            $offset -= $length;
        }

        return $lower;
    }
}
