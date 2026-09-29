<?php

namespace Tests\Feature;

use App\Filament\Resources\GroupCounsellingLogs\Pages\CreateGroupCounsellingLog;
use App\Filament\Resources\GroupCounsellingLogs\Pages\EditGroupCounsellingLog;
use App\Filament\Resources\GroupCounsellingLogs\Pages\ViewGroupCounsellingLog;
use App\Filament\Widgets\ClientStatsOverview;
use App\Models\Client;
use App\Models\Group;
use App\Models\GroupCounsellingLog;
use App\Models\IntakeV2;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class GroupCounsellingLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_choosing_a_group_loads_its_members_and_saves_attendance(): void
    {
        $this->actingAs(User::factory()->create());
        $group = Group::factory()->create();
        [$present, $absent] = Client::factory()->count(2)->create();
        $group->clients()->attach([$present->id, $absent->id]);

        $form = Livewire::test(CreateGroupCounsellingLog::class)
            ->fillForm([
                'group_id' => $group->id,
                'session_number' => 2,
                'session_theme' => 'Values',
                'part1_subjective_objective' => 'Members presented...',
            ]);

        $members = collect($form->get('data.members'))->values();
        $this->assertSame([$present->id, $absent->id], $members->pluck('client_id')->all());

        $form->set('data.members', $members->map(fn (array $member): array => $member['client_id'] === $absent->id
            ? [...$member, 'attended' => false, 'attendance_notes' => 'Sick']
            : $member)->all())
            ->call('create')
            ->assertHasNoFormErrors();

        $log = GroupCounsellingLog::firstOrFail();

        $this->assertSame($group->id, $log->group_id);
        $this->assertSame(2, $log->session_number);
        $this->assertCount(2, $log->members);
        $this->assertSame(['Present', 'Absent – Sick'], $log->memberRows()->pluck('attendance')->all());
    }

    public function test_group_is_required(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateGroupCounsellingLog::class)
            ->fillForm(['group_id' => null])
            ->call('create')
            ->assertHasFormErrors(['group_id' => 'required']);
    }

    public function test_creating_a_log_sets_written_at_from_the_session_start(): void
    {
        $this->actingAs(User::factory()->create());
        $group = Group::factory()->create();

        Livewire::test(CreateGroupCounsellingLog::class)
            ->fillForm(['group_id' => $group->id, 'session_date' => '2026-09-18', 'start_time' => '10:00'])
            ->call('create')
            ->assertHasNoFormErrors();

        $writtenAt = GroupCounsellingLog::firstOrFail()->written_at;
        $sessionStart = Carbon::parse('2026-09-18 10:00');

        $this->assertTrue($writtenAt->gte($sessionStart->copy()->addHours(3)));
        $this->assertTrue($writtenAt->lte($sessionStart->copy()->addHours(24)));
    }

    public function test_editing_sets_written_at_when_the_session_start_is_added_and_changed(): void
    {
        $this->actingAs(User::factory()->create());
        $log = GroupCounsellingLog::factory()->create(['session_date' => null, 'start_time' => null, 'written_at' => null]);

        $edit = fn (array $data) => Livewire::test(EditGroupCounsellingLog::class, ['record' => $log->getRouteKey()])
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

    public function test_member_rows_take_gender_and_age_from_the_intake(): void
    {
        $client = Client::factory()->create(['name' => 'Jane']);
        IntakeV2::factory()->create(['client_id' => $client->id, 'sex' => 'Female', 'age' => 24]);
        $log = GroupCounsellingLog::factory()->create([
            'members' => [['client_id' => $client->id, 'attended' => true, 'participation_level' => 'High']],
        ]);

        $row = $log->memberRows()->first();

        $this->assertSame('Jane (J01)', $row['name']);
        $this->assertSame('Female', $row['gender']);
        $this->assertSame('24', $row['age']);
        $this->assertSame('High', $row['participation']);
    }

    public function test_member_rows_append_the_client_code_to_the_name(): void
    {
        $client = Client::factory()->create(['name' => 'Jane', 'client_code' => 'J01']);
        $autoCoded = Client::factory()->create(['name' => 'Ben']);
        $log = GroupCounsellingLog::factory()->create([
            'members' => [['client_id' => $client->id], ['client_id' => $autoCoded->id]],
        ]);

        $this->assertSame(['Jane (J01)', 'Ben (B02)'], $log->memberRows()->pluck('name')->all());
    }

    public function test_pdf_template_renders_with_and_without_members(): void
    {
        $client = Client::factory()->create();
        $withMembers = GroupCounsellingLog::factory()->create(['members' => [['client_id' => $client->id]]]);
        $withoutMembers = GroupCounsellingLog::factory()->create();

        foreach ([$withMembers, $withoutMembers] as $log) {
            $this->assertStringStartsWith('%PDF', Pdf::loadView('pdf.group-counselling-log', ['log' => $log])->output());
        }
    }

    public function test_download_actions_work_for_a_log_with_members(): void
    {
        Http::fake(['*/forms/libreoffice/convert' => Http::response('%PDF-fake')]);

        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create(['name' => 'Jane Client']);
        $log = GroupCounsellingLog::factory()->create([
            'members' => [['client_id' => $client->id, 'attended' => true]],
            'part1_subjective_objective' => '<p>First paragraph.</p><p>Second paragraph.</p>',
        ]);

        foreach (['downloadPdf', 'downloadDocx'] as $action) {
            Livewire::test(ViewGroupCounsellingLog::class, ['record' => $log->getRouteKey()])
                ->assertActionVisible($action)
                ->callAction($action)
                ->assertHasNoActionErrors();
        }
    }

    public function test_download_docx_works_for_a_log_without_members(): void
    {
        $this->actingAs(User::factory()->create());
        $log = GroupCounsellingLog::factory()->create();

        Livewire::test(ViewGroupCounsellingLog::class, ['record' => $log->getRouteKey()])
            ->callAction('downloadDocx')
            ->assertHasNoActionErrors();
    }

    public function test_dashboard_shows_total_group_counselling_hours(): void
    {
        $this->actingAs(User::factory()->create());
        GroupCounsellingLog::factory()->create(['duration_hours' => 1.5]);
        GroupCounsellingLog::factory()->create(['duration_hours' => 2]);

        Livewire::test(ClientStatsOverview::class)
            ->assertSee('Group counselling hours')
            ->assertSee('3.5 / 30')
            ->assertSee('26.5 h to go');
    }
}
