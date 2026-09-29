<?php

namespace Tests\Feature;

use App\Jobs\SummarizeReport;
use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use App\Models\ReportSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReportSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function makeLog(array $attributes = []): IndividualCounsellingLog
    {
        return IndividualCounsellingLog::create([
            'client_id' => Client::factory()->create()->id,
            'part1_subjective_objective' => '<p>Client reports exam stress.</p><p>Poor sleep.</p>',
            'part2_assessment' => '<p>Mild anxiety &amp; low mood.</p>',
            ...$attributes,
        ]);
    }

    public function test_saving_a_log_queues_a_summary_on_the_summaries_queue(): void
    {
        Queue::fake();

        $log = $this->makeLog();

        Queue::assertPushedOn('summaries', SummarizeReport::class, fn (SummarizeReport $job): bool => $job->report->is($log));
    }

    public function test_the_job_pipes_plain_text_to_claude_and_stores_the_summary(): void
    {
        Process::fake(['*' => Process::result("  Client has exam stress.  \n")]);

        $log = $this->makeLog();

        Process::assertRan(fn (PendingProcess $process): bool => $process->command === ['claude', '-p', SummarizeReport::PROMPT]
            && $process->path === sys_get_temp_dir()
            && str_contains($process->input, "Client reports exam stress.\nPoor sleep.")
            && str_contains($process->input, 'Mild anxiety & low mood.')
            && ! str_contains($process->input, '<p>'));

        $this->assertSame('Client has exam stress.', $log->reportSummary->summary);
    }

    public function test_saving_again_overwrites_the_single_summary(): void
    {
        Process::fake(['*' => Process::sequence()->push(Process::result('First.'))->push(Process::result('Second.'))]);

        $log = $this->makeLog();
        $log->update(['part4_evaluation' => '<p>Improved.</p>']);

        $this->assertSame(1, ReportSummary::count());
        $this->assertSame('Second.', $log->fresh()->reportSummary->summary);
    }

    public function test_a_failed_claude_run_throws_so_the_job_is_retried(): void
    {
        Process::fake(['*' => Process::result(errorOutput: 'Invalid API key', exitCode: 1)]);

        $this->expectException(ProcessFailedException::class);

        $this->makeLog();
    }

    public function test_deleting_a_log_deletes_its_summary(): void
    {
        $log = $this->makeLog();
        $this->assertSame(1, ReportSummary::count());

        $log->delete();

        $this->assertSame(0, ReportSummary::count());
    }
}
