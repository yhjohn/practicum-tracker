<?php

namespace App\Jobs;

use App\Models\IndividualCounsellingLog;
use App\Models\ProgressJourney;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Has `claude -p` draft the progress journey entry for a counselling log.
 * Runs on the "summaries" queue, which only the practicum_claude container
 * works. Entries the counsellor has edited by hand are never overwritten.
 */
class DraftProgressJourney implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public const PROMPT = 'The counselling session report on stdin has four parts. '
        .'Draft the counsellor\'s progress journey entry for this session. '
        .'Reply with only a JSON object, no code fences, with these keys: '
        .'background (background of the presenting issues), case_conceptualisation, interventions, '
        .'progress_summary (the client\'s progress in this session). '
        .'Each value is 1 to 3 short paragraphs of HTML using <p> only, based on the report alone, '
        .'in the same language as the report. Use null when the report has nothing for a key.';

    private const FIELDS = ['background', 'case_conceptualisation', 'interventions', 'progress_summary'];

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300];

    public bool $deleteWhenMissingModels = true;

    public function __construct(public IndividualCounsellingLog $log)
    {
        $this->onQueue('summaries');
    }

    /**
     * Collapse repeated saves of the same log into one pending job; the job
     * reads the log fresh when it runs, so it still sees the latest edit.
     */
    public function uniqueId(): string
    {
        return (string) $this->log->getKey();
    }

    public function handle(): void
    {
        if (! $this->log->hasReportContent() || ! $this->canWrite($this->log->progressJourney)) {
            return;
        }

        // Run outside the project so Claude cannot read the code or .env.
        $result = Process::path(sys_get_temp_dir())
            ->input($this->log->summaryText())
            ->timeout(80)
            ->run(['claude', '-p', self::PROMPT])
            ->throw();

        // Claude sometimes wraps the JSON in prose or code fences, so take the outermost object.
        $draft = preg_match('/\{.*\}/s', $result->output(), $match) ? json_decode($match[0], true) : null;

        if (! is_array($draft)) {
            $this->fail(new RuntimeException('Claude did not return the progress journey as JSON: '.Str::limit(trim($result->output()), 500)));

            return;
        }

        // Check again: the counsellor may have edited the entry while Claude was running.
        $journey = $this->log->progressJourney()->first();

        if (! $this->canWrite($journey)) {
            return;
        }

        $journey ??= new ProgressJourney([
            'client_id' => $this->log->client_id,
            'individual_counselling_log_id' => $this->log->id,
        ]);

        $now = now();

        $journey->fill(Arr::only($draft, self::FIELDS));
        $journey->ai_drafted_at = $now;
        $journey->updated_at = $now;
        $journey->save();
    }

    private function canWrite(?ProgressJourney $journey): bool
    {
        return $journey === null || $journey->isUntouchedAiDraft();
    }
}
