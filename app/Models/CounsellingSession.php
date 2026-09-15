<?php

namespace App\Models;

use App\Enums\SessionType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CounsellingSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'duration_minutes' => 'integer',
        'type' => SessionType::class,
        'attachments' => 'array',
    ];

    public function sessionable(): MorphTo
    {
        return $this->morphTo();
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'session_attendance')
            ->withPivot(['attended', 'notes'])
            ->withTimestamps();
    }

    public function isIndividual(): bool
    {
        return $this->type === SessionType::Individual;
    }

    public function isGroup(): bool
    {
        return $this->type === SessionType::Group;
    }

    /**
     * Parse a free-text duration (e.g. "1h 15m", "1h", "45m") into total minutes.
     */
    public static function parseDurationToMinutes(?string $duration): ?int
    {
        if (blank($duration)) {
            return null;
        }

        if (is_numeric($duration)) {
            return (int) $duration;
        }

        preg_match('/(?:(\d+)\s*h)?\s*(?:(\d+)\s*m)?/i', trim($duration), $matches);

        $hours = (int) ($matches[1] ?? 0);
        $minutes = (int) ($matches[2] ?? 0);

        $total = ($hours * 60) + $minutes;

        return $total > 0 ? $total : null;
    }

    /**
     * Format total minutes into a "1h 15m" style string.
     */
    public static function formatMinutesToDuration(?int $minutes): ?string
    {
        if ($minutes === null || $minutes <= 0) {
            return null;
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return trim(($hours > 0 ? "{$hours}h " : '').($remainingMinutes > 0 ? "{$remainingMinutes}m" : ''));
    }

    /**
     * Calculate the whole number of minutes between a start and end time, handling overnight spans.
     */
    public static function minutesBetween(?string $startTime, ?string $endTime): ?int
    {
        if (blank($startTime) || blank($endTime)) {
            return null;
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end);
    }
}
