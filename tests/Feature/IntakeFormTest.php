<?php

namespace Tests\Feature;

use App\Enums\IntakeStatus;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Livewire\PublicIntakeForm;
use App\Models\Client;
use App\Models\Intake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class IntakeFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_intake_link_action_creates_a_pending_intake_and_a_valid_signed_url(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->callAction('generateIntakeLink')
            ->assertHasNoActionErrors();

        $intake = $client->intakes()->sole();

        $this->assertSame(IntakeStatus::Pending, $intake->status);
    }

    public function test_public_intake_form_is_accessible_via_a_valid_signed_url(): void
    {
        $intake = Intake::factory()->create();

        $url = URL::temporarySignedRoute('intake.show', now()->addDays(7), ['intake' => $intake->id]);

        $this->get($url)->assertOk();
    }

    public function test_expired_or_tampered_signed_url_is_rejected(): void
    {
        $intake = Intake::factory()->create();

        $url = URL::temporarySignedRoute('intake.show', now()->subDay(), ['intake' => $intake->id]);

        $this->get($url)->assertForbidden();
    }

    public function test_submitting_intake_marks_it_submitted_and_backfills_null_client_fields(): void
    {
        $client = Client::factory()->create(['dob' => null, 'emergency_contact' => null, 'emergency_contact_phone' => null]);
        $intake = Intake::factory()->for($client)->create();

        Livewire::test(PublicIntakeForm::class, ['intake' => $intake])
            ->fillForm([
                'dob' => '1990-01-01',
                'emergency_name' => 'Jane Doe',
                'emergency_phone' => '555-0100',
                'signature' => 'data:image/png;base64,iVBORw0KGgo=',
            ])
            ->call('submit')
            ->assertHasNoFormErrors();

        $intake->refresh();
        $client->refresh();

        $this->assertSame(IntakeStatus::Submitted, $intake->status);
        $this->assertNotNull($intake->submitted_at);
        $this->assertSame('1990-01-01', $client->dob->format('Y-m-d'));
        $this->assertSame('Jane Doe', $client->emergency_contact);
        $this->assertSame('555-0100', $client->emergency_contact_phone);
    }

    public function test_submitting_intake_does_not_overwrite_existing_client_fields(): void
    {
        $client = Client::factory()->create(['emergency_contact' => 'Existing Contact']);
        $intake = Intake::factory()->for($client)->create();

        Livewire::test(PublicIntakeForm::class, ['intake' => $intake])
            ->fillForm([
                'emergency_name' => 'New Contact',
                'signature' => 'data:image/png;base64,iVBORw0KGgo=',
            ])
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertSame('Existing Contact', $client->refresh()->emergency_contact);
    }

    public function test_visiting_an_already_submitted_intake_does_not_render_the_form_again(): void
    {
        $intake = Intake::factory()->submitted()->create();

        Livewire::test(PublicIntakeForm::class, ['intake' => $intake])
            ->assertSee('already been submitted');
    }
}
