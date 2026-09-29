<?php

namespace App\Models;

use App\Models\Concerns\HasLogBookEntry;
use App\Models\Concerns\HasPracticumWeek;
use App\Models\Concerns\HasReportSummary;
use App\Models\Concerns\HasWrittenAt;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndividualCounsellingLog extends Model
{
    use HasLogBookEntry;
    use HasPracticumWeek;
    use HasReportSummary;
    use HasWrittenAt;

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

    public function summaryText(): string
    {
        return collect([
            'Part 1: Issues / Problems Presented' => $this->part1_subjective_objective,
            'Part 2: Assessment of Issues / Problems' => $this->part2_assessment,
            'Part 3: Treatment Planning and Action' => $this->part3_treatment_plan,
            'Part 4: Evaluation of Counseling Session' => $this->part4_evaluation,
        ])->map(fn (?string $html, string $heading): string => $heading."\n".trim(html_entity_decode(strip_tags(preg_replace('#<(br|/p|/li)[^>]*>#i', "\n", (string) $html)))))
            ->implode("\n\n");
    }
}
