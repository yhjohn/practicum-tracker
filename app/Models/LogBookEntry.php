<?php

namespace App\Models;

use App\Enums\LogBookActivity;
use App\Models\Concerns\HasPracticumWeek;
use Database\Factories\LogBookEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

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
     * Create, update or remove the log book rows that mirror an individual or group log:
     * the counselling session itself and one hour of administration ending when the report is written.
     */
    public static function syncFrom(IndividualCounsellingLog|GroupCounsellingLog $log): void
    {
        if ($log->session_date === null) {
            $log->logBookEntries()->delete();

            return;
        }

        $details = $log instanceof IndividualCounsellingLog
            ? [
                'activity' => LogBookActivity::IndividualCounselling,
                'description' => 'Individual counselling'.($log->session_number ? " – Session {$log->session_number}" : ''),
                'client_code' => $log->client?->client_code,
                'number_of_clients' => 1,
            ]
            : [
                'activity' => LogBookActivity::GroupCounselling,
                'description' => trim('Group counselling'.($log->session_number ? " – Session {$log->session_number}" : '').($log->session_theme ? ": {$log->session_theme}" : '')),
                'client_code' => $log->group?->name,
                'number_of_clients' => collect($log->members)->filter(fn (array $member): bool => $member['attended'] ?? true)->count(),
            ];

        $timing = [
            'session_date' => $log->session_date,
            'start_time' => $log->start_time?->format('H:i'),
        ];

        $log->logBookEntries()->updateOrCreate(['activity' => $details['activity']], $details + $timing + [
            'duration_hours' => $log->duration_hours ?? 0,
        ]);

        // Administration is the hour spent writing the report, so it ends at written_at.
        $adminTiming = $log->written_at === null ? $timing : [
            'session_date' => $log->written_at->copy()->subHour()->toDateString(),
            'start_time' => $log->written_at->copy()->subHour()->format('H:i'),
        ];

        $log->logBookEntries()->updateOrCreate(['activity' => LogBookActivity::ManagementAndAdministration], $adminTiming + [
            'description' => 'Administration – '.$details['description'],
            'client_code' => $details['client_code'],
            'number_of_clients' => 0,
            'duration_hours' => 1,
        ]);
    }

    /**
     * Hours booked so far against the PROGRESS DASHBOARD targets on the XLSX
     * template's SUMMARY sheet (config('practicum.targets')): one row per
     * component, then the three totals the program sets independently of them.
     *
     * @return Collection<int, array{label: string, target: int, completed: float, remaining: float, percent: ?float, status: string}>
     */
    public static function progressSummary(): Collection
    {
        $hoursByActivity = static::query()
            ->selectRaw('activity, SUM(duration_hours) as total')
            ->groupBy('activity')
            ->get()
            ->mapWithKeys(fn (self $row): array => [$row->activity->value => (float) $row->total]);

        $directContactComponents = [
            'individual_counselling' => ['Individual Counselling', [LogBookActivity::IndividualCounselling]],
            'group_counselling' => ['Group Counselling', [LogBookActivity::GroupCounselling]],
            'career_family_couple_counselling' => ['Career / Family / Couple Counselling', [LogBookActivity::CareerFamilyCoupleCounselling]],
        ];

        $otherComponents = [
            'professional_development' => ['Professional Development', [LogBookActivity::ProfessionalDevelopment]],
            'guidance_and_administration' => ['Guidance & Administration', [LogBookActivity::GuidanceActivities, LogBookActivity::ManagementAndAdministration]],
            'supervision' => ['Supervision', [LogBookActivity::Supervision]],
            'face_to_face_seminar' => ['Face-to-Face Seminar', [LogBookActivity::FaceToFaceSeminar]],
        ];

        $targets = config('practicum.targets');
        $completedFor = fn (array $activities): float => collect($activities)
            ->sum(fn (LogBookActivity $activity): float => $hoursByActivity->get($activity->value, 0.0));

        $rows = collect();
        $directContact = 0.0;
        $other = 0.0;

        foreach ($directContactComponents as $key => [$label, $activities]) {
            $completed = $completedFor($activities);
            $directContact += $completed;
            $rows->push(self::progressRow($label, $targets[$key], $completed));
        }

        foreach ($otherComponents as $key => [$label, $activities]) {
            $completed = $completedFor($activities);
            $other += $completed;
            $rows->push(self::progressRow($label, $targets[$key], $completed));
        }

        $rows->push(self::progressRow('TOTAL Direct Client Contact', $targets['direct_client_contact_total'], $directContact));
        $rows->push(self::progressRow('TOTAL Other Activities', $targets['other_activities_total'], $other));
        $rows->push(self::progressRow('GRAND TOTAL', $targets['grand_total'], $directContact + $other));

        return $rows;
    }

    /**
     * @return array{label: string, target: int, completed: float, remaining: float, percent: ?float, status: string}
     */
    private static function progressRow(string $label, int $target, float $completed): array
    {
        return [
            'label' => $label,
            'target' => $target,
            'completed' => $completed,
            'remaining' => max($target - $completed, 0),
            'percent' => $target === 0 ? null : min(100, round($completed / $target * 100)),
            'status' => match (true) {
                $target === 0 => '—',
                $completed >= $target => 'Complete',
                $completed >= $target * 0.5 => 'In Progress',
                default => 'Below Target',
            },
        ];
    }
}
