<?php

namespace Tests\Feature;

use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Filament\Resources\MentalStatusExaminations\Pages\CreateMentalStatusExamination;
use App\Filament\Resources\MentalStatusExaminations\Pages\EditMentalStatusExamination;
use App\Filament\Resources\MentalStatusExaminations\Pages\ViewMentalStatusExamination;
use App\Models\Client;
use App\Models\MentalStatusExamination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MentalStatusExaminationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_has_one_mse(): void
    {
        $client = Client::factory()->create();
        $mse = MentalStatusExamination::factory()->create(['client_id' => $client->id]);

        $this->assertTrue($client->refresh()->mse->is($mse));
    }

    public function test_creating_an_mse_requires_a_client(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateMentalStatusExamination::class)
            ->fillForm(['client_id' => null])
            ->call('create')
            ->assertHasFormErrors(['client_id' => 'required']);
    }

    public function test_creating_an_mse_saves_the_ticked_fields(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateMentalStatusExamination::class)
            ->fillForm([
                'client_id' => $client->id,
                'date_of_interview' => '2026-09-22',
                'dress_grooming' => ['Neatly dressed/groomed'],
                'eye_contact' => ['Good', 'Variable'],
                'mood' => ['Other'],
                'mood_other' => 'Elated',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $mse = MentalStatusExamination::firstOrFail();

        $this->assertSame($client->id, $mse->client_id);
        $this->assertSame(['Neatly dressed/groomed'], $mse->dress_grooming);
        $this->assertSame(['Good', 'Variable'], $mse->eye_contact);
        $this->assertSame('Elated', $mse->mood_other);
    }

    public function test_a_client_can_only_have_one_mse(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        MentalStatusExamination::factory()->create(['client_id' => $client->id]);

        Livewire::test(CreateMentalStatusExamination::class)
            ->fillForm(['client_id' => $client->id])
            ->call('create')
            ->assertHasFormErrors(['client_id']);
    }

    public function test_editing_an_mse_updates_its_fields(): void
    {
        $this->actingAs(User::factory()->create());
        $mse = MentalStatusExamination::factory()->create();

        Livewire::test(EditMentalStatusExamination::class, ['record' => $mse->getRouteKey()])
            ->fillForm(['strengths' => 'Good verbal skills.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Good verbal skills.', $mse->fresh()->strengths);
    }

    public function test_download_pdf_action_is_visible_and_callable(): void
    {
        $this->actingAs(User::factory()->create());
        $mse = MentalStatusExamination::factory()->create();

        Livewire::test(ViewMentalStatusExamination::class, ['record' => $mse->getRouteKey()])
            ->assertActionVisible('downloadPdf')
            ->callAction('downloadPdf')
            ->assertHasNoActionErrors();
    }

    public function test_client_view_shows_the_mse_section(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        MentalStatusExamination::factory()->create([
            'client_id' => $client->id,
            'dress_grooming' => ['Neatly dressed/groomed'],
        ]);

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->assertSee('Neatly dressed/groomed');
    }
}
