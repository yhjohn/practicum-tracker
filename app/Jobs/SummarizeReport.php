<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;

/**
 * Runs `claude -p` on a report and stores the result. Only the practicum_claude
 * container works the "summaries" queue, so the CLI never runs in the app container.
 */
class SummarizeReport implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public const PROMPT = 'Summarise the counselling session report given on stdin in 3 to 5 sentences. '
        .'Cover the presenting issue, assessment, plan and progress. '
        .'Reply with the summary only, as plain text, in the same language as the report.';

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public bool $deleteWhenMissingModels = true;

    /**
     * @param  Model  $report  A model using the HasReportSummary trait.
     */
    public function __construct(public Model $report)
    {
        $this->onQueue('summaries');
    }

    /**
     * Collapse repeated saves of the same report into one pending job; the job
     * reads the report fresh when it runs, so it still sees the latest edit.
     */
    public function uniqueId(): string
    {
        return $this->report::class.':'.$this->report->getKey();
    }

    public function handle(): void
    {
        // Run outside the project so Claude cannot read the code or .env.
        $result = Process::path(sys_get_temp_dir())
            ->input($this->report->summaryText())
            ->timeout(80)
            ->run(['claude', '-p', self::PROMPT])
            ->throw();

        $this->report->reportSummary()->updateOrCreate([], ['summary' => trim($result->output())]);
    }
}
