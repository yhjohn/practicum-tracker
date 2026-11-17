<?php

namespace Tests\Feature;

use App\Exports\ProgressJourneyExport;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Pages\ManageClientProgressJourneys;
use App\Filament\Resources\Clients\Resources\ProgressJourneys\Pages\CreateProgressJourney;
use App\Filament\Resources\Clients\Resources\ProgressJourneys\Pages\EditProgressJourney;
use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use App\Models\ProgressJourney;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class ProgressJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_client_has_many_progress_journeys(): void
    {
        $client = Client::factory()->create();
        ProgressJourney::factory()->count(2)->create(['client_id' => $client->id]);

        $this->assertCount(2, $client->refresh()->progressJourneys);
    }

    public function test_page_lists_only_the_clients_own_progress_journeys(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        $own = ProgressJourney::factory()->count(2)->create(['client_id' => $client->id]);
        $other = ProgressJourney::factory()->create();

        Livewire::test(ManageClientProgressJourneys::class, ['record' => $client->getRouteKey()])
            ->assertOk()
            ->assertCanSeeTableRecords($own)
            ->assertCanNotSeeTableRecords([$other]);
    }

    public function test_creating_a_progress_journey_saves_it_for_the_client_session(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        $log = IndividualCounsellingLog::create(['client_id' => $client->id, 'session_number' => 1]);

        Livewire::test(CreateProgressJourney::class, ['parentRecord' => $client])
            ->fillForm([
                'individual_counselling_log_id' => $log->id,
                'background' => '<p>Exam stress</p>',
                'case_conceptualisation' => '<p>CBT lens</p>',
                'interventions' => '<p>Thought record</p>',
                'progress_summary' => '<p>More settled</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $journey = $client->progressJourneys()->sole();

        $this->assertTrue($journey->individualCounsellingLog->is($log));
        $this->assertStringContainsString('Exam stress', $journey->background);
        $this->assertStringContainsString('More settled', $journey->progress_summary);
    }

    public function test_session_is_required(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateProgressJourney::class, ['parentRecord' => $client])
            ->fillForm(['individual_counselling_log_id' => null])
            ->call('create')
            ->assertHasFormErrors(['individual_counselling_log_id' => 'required']);
    }

    public function test_a_session_can_only_have_one_progress_journey(): void
    {
        $this->actingAs(User::factory()->create());
        $journey = ProgressJourney::factory()->create();

        Livewire::test(CreateProgressJourney::class, ['parentRecord' => $journey->client])
            ->fillForm(['individual_counselling_log_id' => $journey->individual_counselling_log_id])
            ->call('create')
            ->assertHasFormErrors(['individual_counselling_log_id' => 'unique']);
    }

    public function test_another_clients_session_cannot_be_selected(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        $otherLog = IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id, 'session_number' => 1]);

        Livewire::test(CreateProgressJourney::class, ['parentRecord' => $client])
            ->fillForm(['individual_counselling_log_id' => $otherLog->id])
            ->call('create')
            ->assertHasFormErrors(['individual_counselling_log_id']);

        $this->assertFalse($client->progressJourneys()->exists());
    }

    public function test_editing_a_progress_journey_keeps_its_own_session(): void
    {
        $this->actingAs(User::factory()->create());
        $journey = ProgressJourney::factory()->create();

        Livewire::test(EditProgressJourney::class, ['parentRecord' => $journey->client, 'record' => $journey->getRouteKey()])
            ->fillForm(['interventions' => '<p>Updated interventions</p>'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertStringContainsString('Updated interventions', $journey->refresh()->interventions);
    }

    public function test_clients_table_links_to_progress_journeys(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(ListClients::class)
            ->assertActionHasUrl(
                TestAction::make('progressJourneys')->table($client),
                ClientResource::getUrl('progress-journeys', ['record' => $client]),
            );
    }

    public function test_export_puts_three_sessions_per_block(): void
    {
        $client = Client::factory()->create(['client_code' => 'A01']);
        ProgressJourney::factory()->count(4)->create(['client_id' => $client->id]);
        $client->progressJourneys()->latest('id')->first()->update(['background' => '<p>Fourth session background</p>']);

        $xml = $this->documentXml(ProgressJourneyExport::generate($client->newCollection([$client]), 'test-progress-journey'));

        $this->assertSame(1, substr_count($xml, '<w:tbl>'));
        $this->assertSame(10, substr_count($xml, '<w:tr>'));
        $this->assertSame(2, substr_count($xml, '<w:cantSplit/>'));
        $this->assertSame(2, substr_count($xml, 'Background of the Presenting Issues'));
        $this->assertStringContainsString('Client 1 (A01)', $xml);
        $this->assertStringContainsString('Session 4', $xml);
        $this->assertStringContainsString('Fourth session background', $xml);
        $this->assertStringNotContainsString('${progress_table}', $xml);
    }

    public function test_export_orders_sessions_by_session_number(): void
    {
        $client = Client::factory()->create();
        foreach ([2, 1] as $sessionNumber) {
            $log = IndividualCounsellingLog::create(['client_id' => $client->id, 'session_number' => $sessionNumber]);
            ProgressJourney::factory()->create(['client_id' => $client->id, 'individual_counselling_log_id' => $log->id]);
        }

        $xml = $this->documentXml(ProgressJourneyExport::generate($client->newCollection([$client]), 'test-progress-journey'));

        $this->assertLessThan(strpos($xml, 'Session 2'), strpos($xml, 'Session 1'));
    }

    public function test_export_all_numbers_each_client(): void
    {
        $first = Client::factory()->create(['client_code' => 'A01']);
        $second = Client::factory()->create(['client_code' => 'B02']);
        ProgressJourney::factory()->create(['client_id' => $first->id]);
        ProgressJourney::factory()->create(['client_id' => $second->id]);

        $xml = $this->documentXml(ProgressJourneyExport::generate(Client::query()->orderBy('id')->get(), 'test-progress-journeys-all'));

        $this->assertStringContainsString('Client 1 (A01)', $xml);
        $this->assertStringContainsString('Client 2 (B02)', $xml);
        $this->assertSame(1, substr_count($xml, 'Client Code'));
    }

    public function test_client_page_downloads_docx(): void
    {
        $this->actingAs(User::factory()->create());
        $journey = ProgressJourney::factory()->create();
        $journey->client->update(['client_code' => 'A01']);

        Livewire::test(ManageClientProgressJourneys::class, ['record' => $journey->client->getRouteKey()])
            ->callAction('exportDocx')
            ->assertFileDownloaded('progress-journey-A01.docx');
    }

    public function test_export_docx_is_hidden_without_entries(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(ManageClientProgressJourneys::class, ['record' => $client->getRouteKey()])
            ->assertActionHidden('exportDocx');
    }

    public function test_clients_list_downloads_all_progress_journeys(): void
    {
        $this->actingAs(User::factory()->create());
        ProgressJourney::factory()->create();

        Livewire::test(ListClients::class)
            ->callAction('exportProgressJourneys')
            ->assertFileDownloaded('progress-journeys-all.docx');
    }

    private function documentXml(string $path): string
    {
        $zip = new ZipArchive;
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        return $xml;
    }
}
