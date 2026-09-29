<?php

namespace App\Models\Concerns;

use App\Jobs\SummarizeReport;
use App\Models\ReportSummary;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Keeps one AI-written summary per report, refreshed in the background by
 * the Claude CLI every time the report is saved.
 */
trait HasReportSummary
{
    /**
     * The plain-text report content handed to Claude for summarising.
     */
    abstract public function summaryText(): string;

    public static function bootHasReportSummary(): void
    {
        static::saved(fn (self $report) => SummarizeReport::dispatch($report));
        static::deleted(fn (self $report) => $report->reportSummary()->delete());
    }

    public function reportSummary(): MorphOne
    {
        return $this->morphOne(ReportSummary::class, 'summarizable');
    }
}
