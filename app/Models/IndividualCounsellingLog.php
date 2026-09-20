<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualCounsellingLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'written_at' => 'datetime',
        'attachments' => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
