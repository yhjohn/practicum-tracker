<?php

namespace Tests\Feature;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Filament\Resources\Clients\Pages\ManageClientSessionPlans;
use App\Filament\Resources\Clients\Resources\SessionPlans\Pages\CreateSessionPlan;
use App\Filament\Resources\Clients\Resources\SessionPlans\Pages\EditSessionPlan;
use App\Models\Client;
use App\Models\SessionPlan;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SessionPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_has_many_session_plans(): void
    {
        $client = Client::factory()->create();
        SessionPlan::factory()->count(2)->create(['client_id' => $client->id]);

        $this->assertCount(2, $client->refresh()->sessionPlans);
    }

    public function test_page_lists_only_the_clients_own_session_plans(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        $ownPlans = SessionPlan::factory()->count(2)->create(['client_id' => $client->id]);
        $otherPlan = SessionPlan::factory()->create();

        Livewire::test(ManageClientSessionPlans::class, ['record' => $client->getRouteKey()])
            ->assertOk()
            ->assertCanSeeTableRecords($ownPlans)
            ->assertCanNotSeeTableRecords([$otherPlan]);
    }

    public function test_table_links_attachments_to_open_in_a_new_tab(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        SessionPlan::factory()->create([
            'client_id' => $client->id,
            'attachments' => ['session-plans/worksheet.pdf'],
        ]);

        Livewire::test(ManageClientSessionPlans::class, ['record' => $client->getRouteKey()])
            ->assertSeeHtml('target="_blank"')
            ->assertSee('worksheet.pdf');
    }

    public function test_creating_a_session_plan_saves_it_for_the_client(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateSessionPlan::class, ['parentRecord' => $client])
            ->fillForm([
                'session_number' => 1,
                'plan' => '<p>Build rapport</p>',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $plan = $client->sessionPlans()->sole();

        $this->assertSame(1, $plan->session_number);
        $this->assertStringContainsString('Build rapport', $plan->plan);
    }

    public function test_creating_a_session_plan_stores_uploaded_pdfs_and_images(): void
    {
        Storage::fake(config('filament.default_filesystem_disk'));
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateSessionPlan::class, ['parentRecord' => $client])
            ->fillForm([
                'session_number' => 1,
                'attachments' => [
                    UploadedFile::fake()->create('worksheet.pdf', 10, 'application/pdf'),
                    UploadedFile::fake()->image('genogram.png'),
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $attachments = $client->sessionPlans()->sole()->attachments;

        $this->assertCount(2, $attachments);
        foreach ($attachments as $path) {
            Storage::disk(config('filament.default_filesystem_disk'))->assertExists($path);
        }
    }

    public function test_session_plan_attachments_reject_other_file_types(): void
    {
        Storage::fake(config('filament.default_filesystem_disk'));
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateSessionPlan::class, ['parentRecord' => $client])
            ->fillForm([
                'session_number' => 1,
                'attachments' => [UploadedFile::fake()->create('notes.docx', 10, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')],
            ])
            ->call('create')
            ->assertHasErrors();

        $this->assertFalse($client->sessionPlans()->exists());
    }

    public function test_session_number_defaults_to_the_next_number(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        SessionPlan::factory()->create(['client_id' => $client->id, 'session_number' => 3]);

        Livewire::test(CreateSessionPlan::class, ['parentRecord' => $client])
            ->assertSchemaStateSet(['session_number' => 4]);
    }

    public function test_session_number_is_required(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateSessionPlan::class, ['parentRecord' => $client])
            ->fillForm(['session_number' => null])
            ->call('create')
            ->assertHasFormErrors(['session_number' => 'required']);
    }

    public function test_session_number_must_be_unique_for_the_client(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        SessionPlan::factory()->create(['client_id' => $client->id, 'session_number' => 1]);

        Livewire::test(CreateSessionPlan::class, ['parentRecord' => $client])
            ->fillForm(['session_number' => 1])
            ->call('create')
            ->assertHasFormErrors(['session_number' => 'unique']);
    }

    public function test_same_session_number_is_allowed_for_a_different_client(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        SessionPlan::factory()->create(['session_number' => 1]);

        Livewire::test(CreateSessionPlan::class, ['parentRecord' => $client])
            ->fillForm(['session_number' => 1])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue($client->sessionPlans()->where('session_number', 1)->exists());
    }

    public function test_editing_a_session_plan_keeps_its_own_session_number(): void
    {
        $this->actingAs(User::factory()->create());
        $plan = SessionPlan::factory()->create(['session_number' => 2]);

        Livewire::test(EditSessionPlan::class, ['parentRecord' => $plan->client, 'record' => $plan->getRouteKey()])
            ->fillForm([
                'session_number' => 2,
                'plan' => '<p>Updated plan</p>',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertStringContainsString('Updated plan', $plan->refresh()->plan);
    }

    public function test_clients_table_links_to_session_plans(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(ListClients::class)
            ->assertActionHasUrl(
                TestAction::make('sessionPlans')->table($client),
                ClientResource::getUrl('session-plans', ['record' => $client]),
            );
    }
}
