<?php

namespace App\Models;

use App\Enums\LogBookActivity;
use App\Models\Concerns\HasPracticumWeek;
use Database\Factories\LogBookEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LogBookEntry extends Model
{
    /** @use HasFactory<LogBookEntryFactory> */
    use HasFactory;

    use HasPracticumWeek;

    protected $guarded = [];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'duration_hours' => 'decimal:2',
        'activity' => LogBookActivity::class,
    ];

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Manual entries are typed in on the Log Book page; the rest mirror a counselling log.
     */
    public function isManual(): bool
    {
        return $this->loggable_id === null;
    }

    /**
     * "09:00 – 10:30" from the start time and duration, or null without a start time.
     */
    public function timeRange(): ?string
    {
        if ($this->start_time === null) {
            return null;
        }

        $end = $this->start_time->copy()->addMinutes((int) round((float) $this->duration_hours * 60));

        return $this->start_time->format('H:i').' – '.$end->format('H:i');
    }

    /**
     * Create, update or remove the log book row that mirrors an individual or group log.
     */
    public static function syncFrom(IndividualCounsellingLog|GroupCounsellingLog $log): void
    {
        if ($log->session_date === null) {
            $log->logBookEntry()->delete();

            return;
        }

        $details = $log instanceof IndividualCounsellingLog
            ? [
                'activity' => LogBookActivity::IndividualCounselling,
                'description' => 'Individual counselling'.($log->session_number ? " – Session {$log->session_number}" : ''),
                'client_code' => $log->client?->name,
                'number_of_clients' => 1,
            ]
            : [
                'activity' => LogBookActivity::GroupCounselling,
                'description' => trim('Group counselling'.($log->session_number ? " – Session {$log->session_number}" : '').($log->session_theme ? ": {$log->session_theme}" : '')),
                'client_code' => $log->group?->name,
                'number_of_clients' => collect($log->members)->filter(fn (array $member): bool => $member['attended'] ?? true)->count(),
            ];

        $log->logBookEntry()->updateOrCreate([], $details + [
            'session_date' => $log->session_date,
            'start_time' => $log->start_time?->format('H:i'),
            'duration_hours' => $log->duration_hours ?? 0,
        ]);
    }
}
