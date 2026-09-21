<?php

namespace Tests\Feature;

use App\Enums\LogBookActivity;
use App\Exports\LogBookExport;
use App\Filament\Resources\LogBookEntries\Pages\ManageLogBookEntries;
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

        $entry = LogBookEntry::sole();

        $this->assertSame(LogBookActivity::IndividualCounselling, $entry->activity);
        $this->assertSame(2, $entry->week);
        $this->assertSame('Jane', $entry->client_code);
        $this->assertSame('09:00 – 10:30', $entry->timeRange());
        $this->assertFalse($entry->isManual());

        $log->update(['duration_hours' => 2, 'session_date' => '2026-09-13']);

        $this->assertCount(1, LogBookEntry::all());
        $this->assertSame(1, LogBookEntry::sole()->week);
        $this->assertEquals(2, LogBookEntry::sole()->duration_hours);

        $log->delete();

        $this->assertCount(0, LogBookEntry::all());
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

        $entry = LogBookEntry::sole();

        $this->assertSame(LogBookActivity::GroupCounselling, $entry->activity);
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
        $mirrored = LogBookEntry::sole();
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

    public function test_export_warns_about_entries_the_template_cannot_hold(): void
    {
        LogBookEntry::factory()->count(16)->create(['session_date' => '2026-09-15']);
        LogBookEntry::factory()->create(['session_date' => '2026-09-01']);

        $warnings = (new LogBookExport)->warnings();

        $this->assertCount(2, $warnings);
        $this->assertStringContainsString('Week 1 has 16 entries', implode(' ', $warnings));
    }
}
