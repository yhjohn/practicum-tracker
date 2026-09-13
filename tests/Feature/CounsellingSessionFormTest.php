<?php

namespace Tests\Feature;

use App\Filament\Resources\CounsellingSessions\Pages\CreateCounsellingSession;
use App\Filament\Resources\CounsellingSessions\Pages\EditCounsellingSession;
use App\Models\Client;
use App\Models\CounsellingSession;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CounsellingSessionFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_select_is_visible_when_type_is_individual(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateCounsellingSession::class)
            ->fillForm(['type' => 'individual'])
            ->assertFormFieldIsVisible('client_id')
            ->assertFormFieldIsHidden('group_id');
    }

    public function test_group_select_is_visible_when_type_is_group(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateCounsellingSession::class)
            ->fillForm(['type' => 'group'])
            ->assertFormFieldIsVisible('group_id')
            ->assertFormFieldIsHidden('client_id');
    }

    public function test_create_form_is_prefilled_from_query_string_for_a_client(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        request()->merge(['type' => 'individual', 'client_id' => $client->id]);

        Livewire::test(CreateCounsellingSession::class)
            ->assertFormSet([
                'type' => 'individual',
                'client_id' => $client->id,
            ]);
    }

    public function test_create_form_is_prefilled_from_query_string_for_a_group(): void
    {
        $this->actingAs(User::factory()->create());
        $group = Group::factory()->create();

        request()->merge(['type' => 'group', 'group_id' => $group->id]);

        Livewire::test(CreateCounsellingSession::class)
            ->assertFormSet([
                'type' => 'group',
                'group_id' => $group->id,
            ]);
    }

    public function test_creating_an_individual_session_sets_client_as_sessionable(): void
    {
        $this->actingAs(User::factory()->create());

        $client = Client::factory()->create();

        Livewire::test(CreateCounsellingSession::class)
            ->fillForm([
                'type' => 'individual',
                'client_id' => $client->id,
                'session_date' => '2026-08-20',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $session = CounsellingSession::firstOrFail();

        $this->assertSame('client', $session->sessionable_type);
        $this->assertSame($client->id, $session->sessionable_id);
    }

    public function test_creating_a_group_session_saves_attendance(): void
    {
        $this->actingAs(User::factory()->create());

        $group = Group::factory()->create();
        $attendee = Client::factory()->create();
        $absentee = Client::factory()->create();
        $group->clients()->attach([$attendee->id, $absentee->id]);

        Livewire::test(CreateCounsellingSession::class)
            ->fillForm([
                'type' => 'group',
                'group_id' => $group->id,
                'session_date' => '2026-08-20',
                'attendee_ids' => [$attendee->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $session = CounsellingSession::firstOrFail();

        $this->assertSame('group', $session->sessionable_type);
        $this->assertSame($group->id, $session->sessionable_id);
        $this->assertTrue($session->attendees()->whereKey($attendee->id)->exists());
        $this->assertFalse($session->attendees()->whereKey($absentee->id)->exists());
    }

    public function test_editing_a_group_session_updates_attendance(): void
    {
        $this->actingAs(User::factory()->create());

        $group = Group::factory()->create();
        $client = Client::factory()->create();
        $group->clients()->attach($client->id);

        $session = CounsellingSession::create([
            'type' => 'group',
            'sessionable_type' => 'group',
            'sessionable_id' => $group->id,
            'session_date' => '2026-08-20',
        ]);

        Livewire::test(EditCounsellingSession::class, ['record' => $session->getRouteKey()])
            ->fillForm(['attendee_ids' => [$client->id]])
            ->call('save');

        $this->assertTrue($session->attendees()->whereKey($client->id)->exists());
    }
}
