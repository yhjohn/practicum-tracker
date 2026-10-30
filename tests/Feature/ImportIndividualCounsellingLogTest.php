<?php

namespace Tests\Feature;

use App\Filament\Resources\IndividualCounsellingLogs\Pages\ListIndividualCounsellingLogs;
use App\Jobs\ImportIndividualCounsellingLog;
use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImportIndividualCounsellingLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function fakeTranscription(array $overrides = []): void
    {
        $report = array_merge([
            'client_code' => 'A15',
            'session_number' => 1,
            'session_date' => '2026-10-17',
            'start_time' => '09:00',
            'place' => 'Sunway Serene Residensi',
            'written_at' => '2026-10-17 23:51',
            'part1_subjective_objective' => '<p>A15 attended counselling. A150 is someone else.</p>',
            'part2_assessment' => '<h3>Incongruence</h3><p>A15’s values.</p>',
            'part3_treatment_plan' => null,
            'part4_evaluation' => '<p>Rapport with A15 developed.</p>',
            'signature' => 'not a column Claude may set',
        ], $overrides);

        Process::fake(['*' => Process::result("```json\n".json_encode($report)."\n```")]);
    }

    private function storeUpload(): string
    {
        Storage::fake('local');
        Storage::disk('local')->put('report-imports/report.pdf', '%PDF-1.4');

        return 'report-imports/report.pdf';
    }

    public function test_the_action_queues_the_import_for_the_chosen_client(): void
    {
        Queue::fake();
        Storage::fake('local');
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create(['client_code' => 'A14']);

        Livewire::test(ListIndividualCounsellingLogs::class)
            ->callAction(TestAction::make('importPdf'), [
                'pdf' => UploadedFile::fake()->create('report.pdf', 10, 'application/pdf'),
                'client_id' => $client->id,
            ])
            ->assertHasNoFormErrors()
            ->assertNotified();

        Queue::assertPushedOn('summaries', ImportIndividualCounsellingLog::class,
            fn (ImportIndividualCounsellingLog $job): bool => $job->client->is($client) && Storage::disk('local')->exists($job->path));
    }

    public function test_the_action_requires_a_pdf_and_a_client(): void
    {
        Queue::fake();
        $this->actingAs(User::factory()->create());

        Livewire::test(ListIndividualCounsellingLogs::class)
            ->callAction(TestAction::make('importPdf'), ['pdf' => null, 'client_id' => null])
            ->assertHasFormErrors(['pdf' => 'required', 'client_id' => 'required']);

        Queue::assertNothingPushed();
    }

    public function test_it_imports_the_report_and_swaps_in_the_chosen_client_code(): void
    {
        $this->fakeTranscription();
        $path = $this->storeUpload();
        $client = Client::factory()->create(['client_code' => 'A14']);

        (new ImportIndividualCounsellingLog($path, $client))->handle();

        $log = IndividualCounsellingLog::sole();

        $this->assertSame($client->id, $log->client_id);
        $this->assertSame(1, $log->session_number);
        $this->assertSame('2026-10-17', $log->session_date->toDateString());
        $this->assertSame('09:00', $log->start_time->format('H:i'));
        $this->assertSame('2026-10-17 23:51:00', $log->written_at->toDateTimeString());
        $this->assertSame('Sunway Serene Residensi', $log->place);
        $this->assertSame('<p>A14 attended counselling. A150 is someone else.</p>', $log->part1_subjective_objective);
        $this->assertSame('<h3>Incongruence</h3><p>A14’s values.</p>', $log->part2_assessment);
        $this->assertNull($log->part3_treatment_plan);
        $this->assertSame('<p>Rapport with A14 developed.</p>', $log->part4_evaluation);
        $this->assertNull($log->signature);
        $this->assertCount(2, $log->logBookEntries);
        Storage::disk('local')->assertMissing($path);
        Process::assertRan(fn ($process): bool => $process->command[0] === 'claude'
            && in_array('sonnet', $process->command, true)
            && $process->path !== base_path());
    }

    public function test_importing_the_same_session_again_updates_the_report(): void
    {
        $client = Client::factory()->create(['client_code' => 'A14']);
        $existing = IndividualCounsellingLog::create(['client_id' => $client->id, 'session_number' => 1, 'place' => 'Old place']);

        $this->fakeTranscription();
        (new ImportIndividualCounsellingLog($this->storeUpload(), $client))->handle();

        $this->assertSame('Sunway Serene Residensi', IndividualCounsellingLog::sole()->place);
        $this->assertSame($existing->id, IndividualCounsellingLog::sole()->id);
    }

    public function test_it_keeps_the_upload_when_the_transcription_is_not_json(): void
    {
        Process::fake(['*' => Process::result('Sorry, I could not read that file.')]);
        $path = $this->storeUpload();

        try {
            (new ImportIndividualCounsellingLog($path, Client::factory()->create()))->handle();
            $this->fail('Expected the import to fail.');
        } catch (\JsonException) {
        }

        $this->assertDatabaseCount('individual_counselling_logs', 0);
        Storage::disk('local')->assertExists($path);
    }
}
