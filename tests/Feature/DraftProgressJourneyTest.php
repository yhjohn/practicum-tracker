<?php

namespace Tests\Feature;

use App\Jobs\DraftProgressJourney;
use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use App\Models\ProgressJourney;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DraftProgressJourneyTest extends TestCase
{
    use RefreshDatabase;

    private const DRAFT = '{"background":"<p>Exam stress.</p>","case_conceptualisation":"<p>Anxious thinking.</p>","interventions":"<p>Thought record.</p>","progress_summary":"<p>Calmer.</p>","extra":"ignored"}';

    /**
     * Fake the CLI so drafting returns $draft while report summaries get plain text.
     */
    private function fakeClaude(string $draft = self::DRAFT): void
    {
        Process::fake(fn (PendingProcess $process) => in_array(DraftProgressJourney::PROMPT, $process->command, true)
            ? Process::result($draft)
            : Process::result('Summary.'));
    }

    private function assertDraftRan(int $times): void
    {
        Process::assertRanTimes(fn (PendingProcess $process): bool => in_array(DraftProgressJourney::PROMPT, $process->command, true), $times);
    }

    private function makeLog(array $attributes = []): IndividualCounsellingLog
    {
        return IndividualCounsellingLog::create([
            'client_id' => Client::factory()->create()->id,
            'session_number' => 1,
            'part1_subjective_objective' => '<p>Client reports exam stress.</p>',
            ...$attributes,
        ]);
    }

    public function test_saving_a_log_queues_a_draft_on_the_summaries_queue(): void
    {
        Queue::fake();

        $log = $this->makeLog();

        Queue::assertPushedOn('summaries', DraftProgressJourney::class, fn (DraftProgressJourney $job): bool => $job->log->is($log));
    }

    public function test_the_job_pipes_the_report_to_claude_and_stores_the_draft(): void
    {
        $this->fakeClaude();

        $log = $this->makeLog();

        Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['claude', '-p', DraftProgressJourney::PROMPT]
            && $process->path === sys_get_temp_dir()
            && str_contains($process->input, 'Client reports exam stress.'));

        $journey = $log->progressJourney;

        $this->assertSame($log->client_id, $journey->client_id);
        $this->assertSame('<p>Exam stress.</p>', $journey->background);
        $this->assertSame('<p>Anxious thinking.</p>', $journey->case_conceptualisation);
        $this->assertSame('<p>Thought record.</p>', $journey->interventions);
        $this->assertSame('<p>Calmer.</p>', $journey->progress_summary);
        $this->assertTrue($journey->isUntouchedAiDraft());
    }

    public function test_prose_around_the_json_is_ignored(): void
    {
        $this->fakeClaude("Here you go:\n```json\n".self::DRAFT."\n```");

        $this->assertSame('<p>Calmer.</p>', $this->makeLog()->progressJourney->progress_summary);
    }

    public function test_a_log_without_report_content_is_not_drafted(): void
    {
        $this->fakeClaude();

        $log = $this->makeLog(['part1_subjective_objective' => '<p> </p>']);

        $this->assertDraftRan(0);
        $this->assertNull($log->progressJourney);
    }

    public function test_resaving_the_log_redrafts_an_untouched_ai_draft(): void
    {
        $this->fakeClaude();
        $log = $this->makeLog();

        $this->fakeClaude(str_replace('Calmer.', 'Much calmer.', self::DRAFT));
        $this->travel(1)->minutes();
        $log->update(['part4_evaluation' => '<p>Improved.</p>']);

        $this->assertSame(1, ProgressJourney::count());
        $this->assertSame('<p>Much calmer.</p>', $log->fresh()->progressJourney->progress_summary);
    }

    public function test_a_hand_edited_journey_is_never_overwritten(): void
    {
        $this->fakeClaude();
        $log = $this->makeLog();

        $this->travel(1)->minutes();
        $log->progressJourney->update(['progress_summary' => '<p>My own words.</p>']);

        $this->travel(1)->minutes();
        $log->update(['part4_evaluation' => '<p>Improved.</p>']);

        $this->assertDraftRan(1);
        $this->assertSame('<p>My own words.</p>', $log->fresh()->progressJourney->progress_summary);
        $this->assertFalse($log->fresh()->progressJourney->isUntouchedAiDraft());
    }

    public function test_a_manually_created_journey_is_never_overwritten(): void
    {
        $this->fakeClaude();
        $log = IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id, 'session_number' => 1]);
        ProgressJourney::factory()->create([
            'client_id' => $log->client_id,
            'individual_counselling_log_id' => $log->id,
            'background' => '<p>Typed by hand.</p>',
        ]);

        $log->update(['part1_subjective_objective' => '<p>Client reports exam stress.</p>']);

        $this->assertDraftRan(0);
        $this->assertSame('<p>Typed by hand.</p>', $log->fresh()->progressJourney->background);
    }

    public function test_a_reply_without_json_fails_the_job_without_saving(): void
    {
        $this->fakeClaude('Sorry, I cannot help with that.');

        $log = $this->makeLog();

        $this->assertNull($log->progressJourney);
    }

    public function test_backfill_migration_queues_drafts_for_logs_without_a_journey(): void
    {
        $withJourney = ProgressJourney::factory()->create()->individualCounsellingLog;
        $withoutJourney = IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id]);
        Queue::fake();

        (require glob(database_path('migrations/*_draft_progress_journeys_for_existing_logs.php'))[0])->up();

        Queue::assertPushed(DraftProgressJourney::class, 1);
        Queue::assertPushed(DraftProgressJourney::class, fn (DraftProgressJourney $job): bool => $job->log->is($withoutJourney));
        Queue::assertNotPushed(DraftProgressJourney::class, fn (DraftProgressJourney $job): bool => $job->log->is($withJourney));
    }
}
