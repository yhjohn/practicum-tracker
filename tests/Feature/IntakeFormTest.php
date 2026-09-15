<?php

namespace Tests\Feature;

use App\Enums\IntakeStatus;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Livewire\PublicClientPortal;
use App\Livewire\PublicConsentForm;
use App\Livewire\PublicIntakeForm;
use App\Models\Client;
use App\Models\Consent;
use App\Models\Intake;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class IntakeFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_client_portal_link_action_creates_a_portal_token_and_passcode(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->callAction('generateClientPortalLink')
            ->assertHasNoActionErrors();

        $client->refresh();

        $this->assertNotNull($client->public_access_token);
        $this->assertNotNull($client->public_access_code);
        $this->assertNotNull($client->public_access_expires_at);
    }

    public function test_client_portal_requires_valid_passcode_before_showing_form_links(): void
    {
        $client = Client::factory()->create([
            'public_access_token' => 'test-token-123',
            'public_access_code' => Hash::make('123456'),
            'public_access_expires_at' => now()->addDays(7),
        ]);

        $url = URL::temporarySignedRoute('client.portal', now()->addDays(7), ['token' => $client->public_access_token]);

        $this->get($url)->assertOk()->assertSee('Enter your access code');

        Livewire::test(PublicClientPortal::class, ['token' => $client->public_access_token])
            ->set('codeInput', '123456')
            ->call('verifyAccessCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'portal');
    }

    public function test_public_intake_form_redirects_to_portal_when_session_has_not_been_verified(): void
    {
        $client = Client::factory()->create();
        $intake = Intake::factory()->for($client)->create();

        $url = URL::temporarySignedRoute('intake.show', now()->addDays(30), ['intake' => $intake->id]);

        $redirect = $this->get($url)
            ->assertRedirectContains('/portal/');

        $this->get($redirect->headers->get('Location'))
            ->assertOk()
            ->assertSee('Enter your access code');
    }

    public function test_client_portal_creates_missing_intake_and_consent_records_before_rendering_links(): void
    {
        $client = Client::factory()->create([
            'public_access_token' => 'portal-token-456',
            'public_access_code' => Hash::make('654321'),
            'public_access_expires_at' => now()->addDays(7),
        ]);

        Livewire::test(PublicClientPortal::class, ['token' => $client->public_access_token])
            ->set('codeInput', '654321')
            ->call('verifyAccessCode')
            ->assertHasNoErrors();

        $client->refresh();

        $this->assertNotNull($client->intake);
        $this->assertNotNull($client->consent);
        $this->assertNotSame(0, $client->intake->id);
        $this->assertNotSame(0, $client->consent->id);
    }

    public function test_expired_or_tampered_signed_url_is_rejected(): void
    {
        $intake = Intake::factory()->create();

        $url = URL::temporarySignedRoute('intake.show', now()->subDay(), ['intake' => $intake->id]);

        $this->get($url)->assertForbidden();
    }

    public function test_entering_an_incorrect_access_code_fails(): void
    {
        $intake = Intake::factory()->withAccessCode('123456')->create();

        Livewire::test(PublicIntakeForm::class, ['intake' => $intake])
            ->set('codeInput', '000000')
            ->call('verifyAccessCode')
            ->assertHasErrors('codeInput')
            ->assertSet('step', 'code');
    }

    public function test_entering_the_correct_access_code_grants_access_to_the_wizard(): void
    {
        $intake = Intake::factory()->withAccessCode('123456')->create();

        Livewire::test(PublicIntakeForm::class, ['intake' => $intake])
            ->set('codeInput', '123456')
            ->call('verifyAccessCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'form');
    }

    public function test_public_intake_form_shows_a_back_to_forms_link(): void
    {
        $intake = Intake::factory()->withAccessCode('123456')->create();

        Livewire::test(PublicIntakeForm::class, ['intake' => $intake])
            ->set('codeInput', '123456')
            ->call('verifyAccessCode')
            ->assertHasNoErrors()
            ->assertSee('Back to forms');
    }

    public function test_public_consent_form_shows_a_back_to_forms_link(): void
    {
        $consent = Consent::factory()->for(Client::factory()->create())->create();

        Livewire::test(PublicConsentForm::class, ['consent' => $consent])
            ->assertSee('Back to forms');
    }

    public function test_too_many_incorrect_attempts_locks_out_further_attempts(): void
    {
        $intake = Intake::factory()->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeForm::class, ['intake' => $intake]);

        for ($i = 0; $i < 5; $i++) {
            $testable->set('codeInput', '000000')->call('verifyAccessCode');
        }

        $testable->set('codeInput', '123456')
            ->call('verifyAccessCode')
            ->assertHasErrors('codeInput')
            ->assertSet('step', 'code');
    }

    public function test_saving_progress_persists_data_without_marking_the_intake_submitted(): void
    {
        $intake = Intake::factory()->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeForm::class, ['intake' => $intake]);
        $this->unlockForm($testable);

        $testable->set('data.name', 'Progress Saved Name')
            ->call('saveProgress');

        $intake->refresh();

        $this->assertSame('Progress Saved Name', $intake->name);
        $this->assertSame(IntakeStatus::Pending, $intake->status);
    }

    public function test_submitting_intake_marks_it_submitted_and_backfills_null_client_fields(): void
    {
        $client = Client::factory()->create(['dob' => null, 'emergency_contact' => null, 'emergency_contact_phone' => null]);
        $intake = Intake::factory()->for($client)->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeForm::class, ['intake' => $intake]);
        $this->unlockForm($testable);

        $testable->fillForm([
            'dob' => '1990-01-01',
            'emergency_name' => 'Jane Doe',
            'emergency_phone' => '555-0100',
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
        $intake = Intake::factory()->for($client)->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeForm::class, ['intake' => $intake]);
        $this->unlockForm($testable);

        $testable->fillForm([
            'emergency_name' => 'New Contact',
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

    public function test_client_infolist_displays_the_full_intake_summary(): void
    {
        $this->actingAs(User::factory()->create());

        $client = Client::factory()->create();
        Intake::factory()->for($client)->submitted()->create([
            'seeking_reason' => 'Feeling overwhelmed at work.',
        ]);

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->assertOk()
            ->assertSee('Feeling overwhelmed at work.');
    }

    private function unlockForm(Testable $testable): void
    {
        $testable->set('codeInput', '123456')
            ->call('verifyAccessCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'form');
    }
}
