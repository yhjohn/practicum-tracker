<?php

namespace Tests\Feature;

use App\Enums\LogBookActivity;
use App\Exports\LogBookExport;
use App\Filament\Resources\LogBookEntries\Pages\ManageLogBookEntries;
use App\Filament\Widgets\PracticumProgressOverview;
use App\Models\Client;
use App\Models\Group;
use App\Models\GroupCounsellingLog;
use App\Models\IndividualCounsellingLog;
use App\Models\LogBookEntry;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class LogBookTest extends TestCase
{
    use RefreshDatabase;

    public function test_individual_log_is_mirrored_into_the_log_book_and_kept_in_sync(): void
    {
        $client = Client::factory()->create(['name' => 'Jane']);
        $log = IndividualCounsellingLog::create([
            'client_id' => $client->id,
            'session_date' => '2026-09-21',
            'start_time' => '09:00',
            'duration_hours' => 1.5,
            'session_number' => 2,
        ]);

        $entry = LogBookEntry::where('activity', LogBookActivity::IndividualCounselling)->sole();

        $this->assertSame(2, $entry->week);
        $this->assertSame('Jane', $entry->client_code);
        $this->assertSame('09:00 – 10:30', $entry->timeRange());
        $this->assertFalse($entry->isManual());

        $log->update(['duration_hours' => 2, 'session_date' => '2026-09-13']);

        $this->assertCount(2, LogBookEntry::all());
        $this->assertSame(1, $entry->refresh()->week);
        $this->assertEquals(2, $entry->duration_hours);

        $log->delete();

        $this->assertCount(0, LogBookEntry::all());
    }

    public function test_each_counselling_log_adds_one_hour_of_administration_at_the_same_time(): void
    {
        $individual = IndividualCounsellingLog::create([
            'client_id' => Client::factory()->create(['name' => 'Jane'])->id,
            'session_date' => '2026-09-21',
            'start_time' => '09:00',
            'duration_hours' => 1.5,
        ]);
        GroupCounsellingLog::factory()->create(['session_date' => '2026-09-22', 'start_time' => '14:00', 'duration_hours' => 2]);

        $admin = LogBookEntry::where('activity', LogBookActivity::ManagementAndAdministration)->orderBy('session_date')->get();

        $this->assertCount(2, $admin);
        $this->assertSame('2026-09-21', $admin[0]->session_date->toDateString());
        $this->assertSame('09:00 – 10:00', $admin[0]->timeRange());
        $this->assertSame('Administration – Individual counselling', $admin[0]->description);
        $this->assertSame('14:00 – 15:00', $admin[1]->timeRange());
        $this->assertFalse($admin[0]->isManual());

        $individual->update(['session_date' => '2026-09-23', 'start_time' => '10:00', 'duration_hours' => 3]);

        $this->assertSame('10:00 – 11:00', $admin[0]->refresh()->timeRange());
        $this->assertSame('2026-09-23', $admin[0]->session_date->toDateString());
        $this->assertCount(4, LogBookEntry::all());

        $individual->update(['session_date' => null]);

        $this->assertCount(2, LogBookEntry::all());
    }

    public function test_log_without_a_session_date_has_no_log_book_entry(): void
    {
        IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id]);

        $this->assertCount(0, LogBookEntry::all());
    }

    public function test_group_log_is_mirrored_with_the_attended_member_count(): void
    {
        $group = Group::factory()->create(['name' => 'GRP-A']);
        GroupCounsellingLog::factory()->create([
            'group_id' => $group->id,
            'session_date' => '2026-09-14',
            'duration_hours' => 2,
            'members' => [['client_id' => 1, 'attended' => true], ['client_id' => 2, 'attended' => false]],
        ]);

        $entry = LogBookEntry::where('activity', LogBookActivity::GroupCounselling)->sole();

        $this->assertSame('GRP-A', $entry->client_code);
        $this->assertSame(1, $entry->number_of_clients);
    }

    public function test_a_manual_activity_can_be_added_by_week_and_date(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageLogBookEntries::class)
            ->callAction('create', [
                'session_date' => '2026-09-22',
                'activity' => LogBookActivity::GuidanceActivities->value,
                'duration_hours' => 2,
                'description' => 'Prepared case notes',
            ])
            ->assertHasNoFormErrors();

        $entry = LogBookEntry::sole();

        $this->assertTrue($entry->isManual());
        $this->assertSame(2, $entry->week);
    }

    public function test_choosing_a_week_fills_in_that_weeks_start_date(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageLogBookEntries::class)
            ->mountAction('create')
            ->fillForm(['week' => 3])
            ->assertSchemaStateSet(['session_date' => '2026-09-27']);
    }

    public function test_the_week_filter_only_lists_entries_from_that_week(): void
    {
        $this->actingAs(User::factory()->create());
        $week1 = LogBookEntry::factory()->create(['session_date' => '2026-09-15']);
        $week2 = LogBookEntry::factory()->create(['session_date' => '2026-09-21']);

        Livewire::test(ManageLogBookEntries::class)
            ->filterTable('week', 2)
            ->assertCanSeeTableRecords([$week2])
            ->assertCanNotSeeTableRecords([$week1]);
    }

    public function test_only_manual_entries_can_be_edited_or_deleted(): void
    {
        $this->actingAs(User::factory()->create());
        IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id, 'session_date' => '2026-09-15']);
        $mirrored = LogBookEntry::first();
        $manual = LogBookEntry::factory()->create();

        Livewire::test(ManageLogBookEntries::class)
            ->assertActionHidden(TestAction::make('edit')->table($mirrored))
            ->assertActionHidden(TestAction::make('delete')->table($mirrored))
            ->assertActionVisible(TestAction::make('edit')->table($manual))
            ->assertActionVisible(TestAction::make('delete')->table($manual));
    }

    public function test_export_fills_the_template_and_keeps_its_formulas(): void
    {
        Storage::fake('local');
        config(['practicum.student.name' => 'Student Name']);
        $client = Client::factory()->create(['name' => 'Jane']);
        IndividualCounsellingLog::create([
            'client_id' => $client->id, 'session_date' => '2026-09-21', 'start_time' => '09:00', 'duration_hours' => 1.5,
        ]);
        LogBookEntry::factory()->create([
            'session_date' => '2026-09-15', 'activity' => LogBookActivity::Supervision, 'duration_hours' => 2, 'description' => 'Peer supervision',
        ]);

        Excel::store(new LogBookExport, 'log.xlsx', 'local');

        $book = IOFactory::load(Storage::disk('local')->path('log.xlsx'));

        $this->assertSame('Student Name', $book->getSheetByName('COVER')->getCell('C7')->getValue());
        $this->assertSame('Jane', $book->getSheetByName('CLIENT TRACKER')->getCell('B5')->getValue());

        $week1 = $book->getSheetByName('W01');
        $this->assertSame('Peer supervision', $week1->getCell('D5')->getValue());
        $this->assertSame('Supervision', $week1->getCell('E5')->getValue());
        $this->assertEquals(2, $week1->getCell('H5')->getValue());
        $this->assertNull($week1->getCell('D6')->getValue());

        $week2 = $book->getSheetByName('W02');
        $this->assertSame('Individual counselling', $week2->getCell('D5')->getValue());
        $this->assertSame('09:00 – 10:30', $week2->getCell('C5')->getValue());
        $this->assertStringStartsWith('=SUMIF', $week2->getCell('G23')->getValue());
        $this->assertStringContainsString('W02', $book->getSheetByName('SUMMARY')->getCell('D8')->getValue());
    }

    public function test_export_warns_about_entries_outside_the_practicum_weeks(): void
    {
        LogBookEntry::factory()->create(['session_date' => '2026-09-01']);

        $this->assertStringContainsString('1 entries fall outside', implode(' ', (new LogBookExport)->warnings()));
    }

    public function test_export_adds_rows_for_a_busy_week_and_keeps_the_totals_covering_them(): void
    {
        Storage::fake('local');
        LogBookEntry::factory()->count(20)->create([
            'session_date' => '2026-09-21', 'activity' => LogBookActivity::Supervision, 'duration_hours' => 1,
        ]);

        Excel::store(new LogBookExport, 'log.xlsx', 'local');

        $book = IOFactory::load(Storage::disk('local')->path('log.xlsx'));
        $week2 = $book->getSheetByName('W02');

        $this->assertSame([], (new LogBookExport)->warnings());
        $this->assertSame(20, $week2->getCell('A24')->getValue());
        $this->assertSame('Supervision', $week2->getCell('E24')->getValue());
        $this->assertNull($week2->getCell('E25')->getValue());
        $this->assertSame('=SUMIF($E$5:$E$24,"Supervision",$H$5:$H$24)', $week2->getCell('G33')->getValue());
        $this->assertEquals(20, $week2->getCell('G33')->getCalculatedValue());
        $this->assertSame("='W02'!G33", $book->getSheetByName('SUMMARY')->getCell('I8')->getValue());
        $this->assertSame('E5:E24', array_key_first($week2->getDataValidationCollection()));
        $this->assertSame("='W01'!G28", $book->getSheetByName('SUMMARY')->getCell('I7')->getValue());
    }

    public function test_progress_summary_totals_hours_by_component_against_their_targets(): void
    {
        $client = Client::factory()->create();
        IndividualCounsellingLog::create(['client_id' => $client->id, 'session_date' => '2026-09-15', 'duration_hours' => 70]);
        LogBookEntry::factory()->create(['activity' => LogBookActivity::GuidanceActivities, 'duration_hours' => 5, 'session_date' => '2026-09-16']);

        $summary = LogBookEntry::progressSummary()->keyBy('label');

        $individual = $summary->get('Individual Counselling');
        $this->assertSame(70, $individual['target']);
        $this->assertEquals(70, $individual['completed']);
        $this->assertEquals(0, $individual['remaining']);
        $this->assertSame(100.0, $individual['percent']);
        $this->assertSame('Complete', $individual['status']);

        $guidance = $summary->get('Guidance & Administration');
        $this->assertEquals(6, $guidance['completed']); // 5 guidance + 1h admin mirrored from the individual log
        $this->assertSame('Below Target', $guidance['status']);

        $career = $summary->get('Career / Family / Couple Counselling');
        $this->assertSame(0, $career['target']);
        $this->assertNull($career['percent']);
        $this->assertSame('—', $career['status']);

        $directContact = $summary->get('TOTAL Direct Client Contact');
        $this->assertSame(96, $directContact['target']);
        $this->assertEquals(70, $directContact['completed']);

        $grandTotal = $summary->get('GRAND TOTAL');
        $this->assertEquals(76, $grandTotal['completed']);
    }

    public function test_the_progress_widget_appears_on_the_log_book_page(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageLogBookEntries::class)
            ->assertSeeLivewire(PracticumProgressOverview::class);
    }
}
