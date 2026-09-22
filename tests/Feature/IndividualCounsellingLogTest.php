<?php

namespace Tests\Feature;

use App\Filament\Resources\IndividualCounsellingLogs\Pages\CreateIndividualCounsellingLog;
use App\Filament\Resources\IndividualCounsellingLogs\Pages\EditIndividualCounsellingLog;
use App\Filament\Resources\IndividualCounsellingLogs\Pages\ViewIndividualCounsellingLog;
use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use Tests\TestCase;

class IndividualCounsellingLogTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        @unlink(resource_path('templates/individual-counselling-log.docx'));

        parent::tearDown();
    }

    public function test_creating_a_log_saves_the_session_details(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateIndividualCounsellingLog::class)
            ->fillForm([
                'client_id' => $client->id,
                'session_date' => '2026-09-18',
                'duration_hours' => 1.5,
                'place' => 'Main Campus Counselling Centre',
                'session_number' => 2,
                'part1_subjective_objective' => 'Client presented with...',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $log = IndividualCounsellingLog::firstOrFail();

        $this->assertSame($client->id, $log->client_id);
        $this->assertSame('Main Campus Counselling Centre', $log->place);
        $this->assertSame(2, $log->session_number);
        $this->assertEquals(1.5, $log->duration_hours);
    }

    public function test_creating_a_log_sets_written_at_from_the_session_start(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateIndividualCounsellingLog::class)
            ->fillForm([
                'client_id' => $client->id,
                'session_date' => '2026-09-18',
                'start_time' => '10:00',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $log = IndividualCounsellingLog::firstOrFail();
        $sessionStart = Carbon::parse('2026-09-18 10:00');

        $this->assertNotNull($log->written_at);
        $this->assertTrue($log->written_at->gte($sessionStart->copy()->addHours(3)));
        $this->assertTrue($log->written_at->lte($sessionStart->copy()->addHours(24)));
        $this->assertNotContains((int) $log->written_at->format('G'), range(0, 6));
    }

    public function test_creating_without_a_session_start_leaves_written_at_null(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateIndividualCounsellingLog::class)
            ->fillForm(['client_id' => $client->id])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNull(IndividualCounsellingLog::firstOrFail()->written_at);
    }

    public function test_editing_sets_written_at_when_the_session_start_is_added_and_changed(): void
    {
        $this->actingAs(User::factory()->create());
        $log = IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id]);

        $edit = fn (array $data) => Livewire::test(EditIndividualCounsellingLog::class, ['record' => $log->getRouteKey()])
            ->fillForm($data)
            ->call('save')
            ->assertHasNoFormErrors();

        $edit(['place' => 'Campus']);
        $this->assertNull($log->fresh()->written_at);

        $edit(['session_date' => '2026-09-18', 'start_time' => '10:00']);
        $writtenAt = $log->fresh()->written_at;
        $this->assertNotNull($writtenAt);
        $this->assertTrue($writtenAt->between(Carbon::parse('2026-09-18 13:00'), Carbon::parse('2026-09-19 10:00')));

        $edit(['place' => 'Elsewhere']);
        $this->assertEquals($writtenAt, $log->fresh()->written_at);

        $edit(['session_date' => '2026-09-25']);
        $this->assertTrue($log->fresh()->written_at->between(Carbon::parse('2026-09-25 13:00'), Carbon::parse('2026-09-26 10:00')));
    }

    public function test_creating_a_log_stores_uploaded_attachments(): void
    {
        Storage::fake(config('filament.default_filesystem_disk'));
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateIndividualCounsellingLog::class)
            ->fillForm([
                'client_id' => $client->id,
                'attachments' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $attachments = IndividualCounsellingLog::firstOrFail()->attachments;

        $this->assertCount(1, $attachments);
        Storage::disk(config('filament.default_filesystem_disk'))->assertExists($attachments[0]);
    }

    public function test_random_written_at_always_respects_its_constraints(): void
    {
        $sessionStarts = [
            Carbon::parse('2026-09-18 08:00'),
            Carbon::parse('2026-09-18 21:30'),
            Carbon::parse('2026-09-18 23:59'),
            Carbon::parse('2026-09-18 00:00'),
            Carbon::parse('2026-09-18 06:30'),
        ];

        foreach ($sessionStarts as $sessionStart) {
            for ($i = 0; $i < 50; $i++) {
                $writtenAt = IndividualCounsellingLog::randomWrittenAt($sessionStart);

                $this->assertTrue($writtenAt->gte($sessionStart->copy()->addHours(3)));
                $this->assertTrue($writtenAt->lte($sessionStart->copy()->addHours(24)));
                $this->assertNotContains((int) $writtenAt->format('G'), range(0, 6));
            }
        }
    }

    public function test_download_pdf_action_is_visible_and_callable(): void
    {
        $this->actingAs(User::factory()->create());
        $log = IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id]);

        Livewire::test(ViewIndividualCounsellingLog::class, ['record' => $log->getRouteKey()])
            ->assertActionVisible('downloadPdf')
            ->callAction('downloadPdf')
            ->assertHasNoActionErrors();
    }

    public function test_pdf_template_renders(): void
    {
        $log = IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id]);

        $pdf = Pdf::loadView('pdf.individual-counselling-log', ['log' => $log])->output();

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_download_docx_action_notifies_when_template_is_missing(): void
    {
        $this->actingAs(User::factory()->create());
        $log = IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id]);

        Livewire::test(ViewIndividualCounsellingLog::class, ['record' => $log->getRouteKey()])
            ->callAction('downloadDocx')
            ->assertNotified('DOCX template missing');
    }

    public function test_download_docx_action_fills_the_template_once_it_exists(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create(['name' => 'Jane Client']);
        $log = IndividualCounsellingLog::create([
            'client_id' => $client->id,
            'part1_subjective_objective' => '<p>First paragraph.</p><p>Second paragraph.</p>',
        ]);

        $this->writeFixtureTemplate();

        Livewire::test(ViewIndividualCounsellingLog::class, ['record' => $log->getRouteKey()])
            ->callAction('downloadDocx')
            ->assertHasNoActionErrors();
    }

    public function test_pdf_template_shows_the_client_code(): void
    {
        $client = Client::factory()->create(['name' => 'Jane Client', 'client_code' => 'J01']);
        $log = IndividualCounsellingLog::create(['client_id' => $client->id]);

        $html = view('pdf.individual-counselling-log', ['log' => $log])->render();

        $this->assertStringContainsString('Jane Client (J01)', $html);
    }

    private function writeFixtureTemplate(): void
    {
        $phpWord = new PhpWord;
        $phpWord->addSection()->addText(
            '${client_name} ${session_date} ${session_number} ${time} ${duration} ${place} ${part1} ${part2} ${part3} ${part4}'
        );

        $path = resource_path('templates/individual-counselling-log.docx');
        @mkdir(dirname($path), recursive: true);

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);
    }
}
