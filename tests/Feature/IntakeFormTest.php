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
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class IntakeFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_intake_link_action_creates_a_pending_intake_with_an_access_code(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->callAction('generateIntakeLink')
            ->assertHasNoActionErrors();

        $intake = $client->intakes()->sole();

        $this->assertSame(IntakeStatus::Pending, $intake->status);
        $this->assertNotNull($intake->access_code);
    }

    public function test_public_intake_form_requires_an_access_code_before_showing_the_wizard(): void
    {
        $intake = Intake::factory()->create();

        $url = URL::temporarySignedRoute('intake.show', now()->addDays(30), ['intake' => $intake->id]);

        $this->get($url)->assertOk()->assertSee('Enter your access code');
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
        $intake = Intake::factory()->for($client)->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeForm::class, ['intake' => $intake]);
        $this->unlockForm($testable);

        $testable->fillForm([
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
