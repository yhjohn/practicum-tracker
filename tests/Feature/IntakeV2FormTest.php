<?php

namespace Tests\Feature;

use App\Enums\IntakeStatus;
use App\Enums\IntakeVersion;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Livewire\PublicClientPortal;
use App\Livewire\PublicIntakeFormV2;
use App\Models\Client;
use App\Models\Intake;
use App\Models\IntakeV2;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class IntakeV2FormTest extends TestCase
{
    use RefreshDatabase;

    private const SIGNATURE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    public function test_new_clients_default_to_intake_version_v2(): void
    {
        $client = Client::factory()->create();

        $this->assertSame(IntakeVersion::V2, $client->intake_version);
    }

    public function test_client_portal_creates_a_v2_intake_for_v2_clients(): void
    {
        $client = Client::factory()->create([
            'public_access_token' => 'portal-token-v2',
            'public_access_code' => Hash::make('654321'),
            'public_access_expires_at' => now()->addDays(7),
            'intake_version' => IntakeVersion::V2,
        ]);

        Livewire::test(PublicClientPortal::class, ['token' => $client->public_access_token])
            ->set('codeInput', '654321')
            ->call('verifyAccessCode')
            ->assertHasNoErrors();

        $client->refresh();

        $this->assertNotNull($client->intakeV2);
        $this->assertNull($client->intake);
    }

    public function test_client_portal_creates_a_v1_intake_for_v1_clients(): void
    {
        $client = Client::factory()->create([
            'public_access_token' => 'portal-token-v1',
            'public_access_code' => Hash::make('654321'),
            'public_access_expires_at' => now()->addDays(7),
            'intake_version' => IntakeVersion::V1,
        ]);

        Livewire::test(PublicClientPortal::class, ['token' => $client->public_access_token])
            ->set('codeInput', '654321')
            ->call('verifyAccessCode')
            ->assertHasNoErrors();

        $client->refresh();

        $this->assertNotNull($client->intake);
        $this->assertNull($client->intakeV2);
    }

    public function test_public_intake_v2_form_redirects_to_portal_when_session_has_not_been_verified(): void
    {
        $client = Client::factory()->create();
        $intake = IntakeV2::factory()->for($client)->create();

        $url = URL::temporarySignedRoute('intake-v2.show', now()->addDays(30), ['intake' => $intake->id]);

        $redirect = $this->get($url)
            ->assertRedirectContains('/portal/');

        $this->get($redirect->headers->get('Location'))
            ->assertOk()
            ->assertSee('Enter your access code');
    }

    public function test_expired_or_tampered_signed_url_is_rejected(): void
    {
        $intake = IntakeV2::factory()->create();

        $url = URL::temporarySignedRoute('intake-v2.show', now()->subDay(), ['intake' => $intake->id]);

        $this->get($url)->assertForbidden();
    }

    public function test_entering_an_incorrect_access_code_fails(): void
    {
        $intake = IntakeV2::factory()->withAccessCode('123456')->create();

        Livewire::test(PublicIntakeFormV2::class, ['intake' => $intake])
            ->set('codeInput', '000000')
            ->call('verifyAccessCode')
            ->assertHasErrors('codeInput')
            ->assertSet('step', 'code');
    }

    public function test_entering_the_correct_access_code_grants_access_to_the_wizard(): void
    {
        $intake = IntakeV2::factory()->withAccessCode('123456')->create();

        Livewire::test(PublicIntakeFormV2::class, ['intake' => $intake])
            ->set('codeInput', '123456')
            ->call('verifyAccessCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'form');
    }

    public function test_public_intake_v2_form_shows_a_back_to_forms_link(): void
    {
        $intake = IntakeV2::factory()->withAccessCode('123456')->create();

        Livewire::test(PublicIntakeFormV2::class, ['intake' => $intake])
            ->set('codeInput', '123456')
            ->call('verifyAccessCode')
            ->assertHasNoErrors()
            ->assertSee('Back to forms');
    }

    public function test_too_many_incorrect_attempts_locks_out_further_attempts(): void
    {
        $intake = IntakeV2::factory()->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeFormV2::class, ['intake' => $intake]);

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
        $intake = IntakeV2::factory()->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeFormV2::class, ['intake' => $intake]);
        $this->unlockForm($testable);

        $testable->set('data.name', 'Progress Saved Name')
            ->call('saveProgress');

        $intake->refresh();

        $this->assertSame('Progress Saved Name', $intake->name);
        $this->assertSame(IntakeStatus::Pending, $intake->status);
    }

    public function test_submitting_intake_marks_it_submitted_and_backfills_null_client_fields(): void
    {
        $client = Client::factory()->create(['emergency_contact' => null, 'emergency_contact_phone' => null]);
        $intake = IntakeV2::factory()->for($client)->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeFormV2::class, ['intake' => $intake]);
        $this->unlockForm($testable);

        $testable->fillForm([
            'emergency_name' => 'Jane Doe',
            'emergency_phone' => '555-0100',
            'address' => "1 Jalan Test\nKuala Lumpur",
            'signature' => self::SIGNATURE,
        ])
            ->call('submit')
            ->assertHasNoFormErrors();

        $intake->refresh();
        $client->refresh();

        $this->assertSame(IntakeStatus::Submitted, $intake->status);
        $this->assertNotNull($intake->submitted_at);
        $this->assertSame('Jane Doe', $client->emergency_contact);
        $this->assertSame('555-0100', $client->emergency_contact_phone);
        $this->assertSame("1 Jalan Test\nKuala Lumpur", $intake->address);
        $this->assertSame(self::SIGNATURE, $intake->signature);
        $this->assertNotNull($intake->signature_date);
    }

    public function test_submitting_intake_without_a_signature_fails_validation(): void
    {
        $intake = IntakeV2::factory()->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeFormV2::class, ['intake' => $intake]);
        $this->unlockForm($testable);

        $testable->call('submit')
            ->assertHasFormErrors(['signature' => 'required']);

        $this->assertSame(IntakeStatus::Pending, $intake->refresh()->status);
    }

    public function test_submitting_intake_does_not_overwrite_existing_client_fields(): void
    {
        $client = Client::factory()->create(['emergency_contact' => 'Existing Contact']);
        $intake = IntakeV2::factory()->for($client)->withAccessCode('123456')->create();

        $testable = Livewire::test(PublicIntakeFormV2::class, ['intake' => $intake]);
        $this->unlockForm($testable);

        $testable->fillForm([
            'emergency_name' => 'New Contact',
            'signature' => self::SIGNATURE,
        ])
            ->call('submit')
            ->assertHasNoFormErrors();

        $this->assertSame('Existing Contact', $client->refresh()->emergency_contact);
    }

    public function test_visiting_an_already_submitted_intake_does_not_render_the_form_again(): void
    {
        $intake = IntakeV2::factory()->submitted()->create();

        Livewire::test(PublicIntakeFormV2::class, ['intake' => $intake])
            ->assertSee('already been submitted');
    }

    public function test_client_infolist_displays_the_full_intake_v2_summary(): void
    {
        $this->actingAs(User::factory()->create());

        $client = Client::factory()->create();
        IntakeV2::factory()->for($client)->submitted()->create([
            'main_concern' => 'Feeling overwhelmed at work.',
        ]);

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->assertOk()
            ->assertSee('Feeling overwhelmed at work.');
    }

    public function test_admin_can_edit_intake_v2_from_the_client_edit_page(): void
    {
        $this->actingAs(User::factory()->create());

        $client = Client::factory()->create();
        $intake = IntakeV2::factory()->for($client)->submitted()->create(['address' => 'Old address']);

        Livewire::test(EditClient::class, ['record' => $client->getRouteKey()])
            ->fillForm(['intakeV2' => ['address' => 'New address', 'main_concern' => 'Edited concern']])
            ->call('save')
            ->assertHasNoFormErrors();

        $intake->refresh();

        $this->assertSame('New address', $intake->address);
        $this->assertSame('Edited concern', $intake->main_concern);
        $this->assertSame(self::SIGNATURE, $intake->signature);
    }

    public function test_admin_can_edit_intake_v1_from_the_client_edit_page(): void
    {
        $this->actingAs(User::factory()->create());

        $client = Client::factory()->create();
        $intake = Intake::factory()->for($client)->submitted()->create(['main_concern' => 'Old concern']);

        Livewire::test(EditClient::class, ['record' => $client->getRouteKey()])
            ->fillForm(['intake' => ['main_concern' => 'Edited V1 concern']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Edited V1 concern', $intake->refresh()->main_concern);
    }

    public function test_client_edit_page_does_not_create_an_intake_v2_when_none_exists(): void
    {
        $this->actingAs(User::factory()->create());

        $client = Client::factory()->create();

        Livewire::test(EditClient::class, ['record' => $client->getRouteKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $client->refresh();

        $this->assertNull($client->intakeV2);
        $this->assertNull($client->intake);
    }

    private function unlockForm(Testable $testable): void
    {
        $testable->set('codeInput', '123456')
            ->call('verifyAccessCode')
            ->assertHasNoErrors()
            ->assertSet('step', 'form');
    }
}
