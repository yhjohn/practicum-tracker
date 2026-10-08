<?php

namespace Tests\Feature;

use App\Enums\ConsentStatus;
use App\Filament\Resources\Groups\Pages\ViewGroup;
use App\Filament\Resources\Groups\RelationManagers\ClientsRelationManager;
use App\Livewire\PublicClientPortal;
use App\Livewire\PublicGroupConsentForm;
use App\Models\Client;
use App\Models\Group;
use App\Models\GroupConsent;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class GroupConsentTest extends TestCase
{
    use RefreshDatabase;

    private const SIGNATURE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    public function test_client_portal_creates_a_group_consent_for_each_group_the_client_is_in(): void
    {
        $client = Client::factory()->create([
            'public_access_code' => Hash::make('654321'),
            'public_access_expires_at' => now()->addDays(7),
        ]);
        $groups = Group::factory()->count(2)->create();
        $client->groups()->attach($groups);

        Livewire::test(PublicClientPortal::class, ['token' => $client->public_access_token])
            ->set('codeInput', '654321')
            ->call('verifyAccessCode')
            ->assertSee('Open group consent form')
            ->assertSee($groups[0]->name)
            ->assertSee($groups[1]->name);

        $this->assertSame(2, $client->groupConsents()->count());
    }

    public function test_client_portal_shows_no_group_consent_for_clients_without_groups(): void
    {
        $client = Client::factory()->create([
            'public_access_code' => Hash::make('654321'),
            'public_access_expires_at' => now()->addDays(7),
        ]);

        Livewire::test(PublicClientPortal::class, ['token' => $client->public_access_token])
            ->set('codeInput', '654321')
            ->call('verifyAccessCode')
            ->assertDontSee('Group consent');

        $this->assertSame(0, $client->groupConsents()->count());
    }

    public function test_group_consent_route_redirects_to_portal_when_not_verified(): void
    {
        $groupConsent = GroupConsent::factory()->create();

        $url = URL::temporarySignedRoute('group-consent.show', now()->addDay(), ['groupConsent' => $groupConsent->id]);

        $this->get($url)->assertRedirect();
    }

    public function test_submitting_the_group_consent_form_marks_it_submitted(): void
    {
        $groupConsent = GroupConsent::factory()->create();

        Livewire::test(PublicGroupConsentForm::class, ['groupConsent' => $groupConsent])
            ->set('data.full_name', 'Jane Doe')
            ->set('data.dob', '2010-01-15')
            ->set('data.agree', true)
            ->set('data.signature', self::SIGNATURE)
            ->set('data.guardian_name', 'John Doe')
            ->set('data.guardian_relationship', 'Father')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('step', 'submitted');

        $groupConsent->refresh();

        $this->assertSame(ConsentStatus::Submitted, $groupConsent->status);
        $this->assertSame('Jane Doe', $groupConsent->full_name);
        $this->assertSame('John Doe', $groupConsent->guardian_name);
        $this->assertNotNull($groupConsent->signature_date);
        $this->assertNotNull($groupConsent->submitted_at);
    }

    public function test_group_consent_form_requires_signature_and_acknowledgement(): void
    {
        $groupConsent = GroupConsent::factory()->create();

        Livewire::test(PublicGroupConsentForm::class, ['groupConsent' => $groupConsent])
            ->set('data.full_name', 'Jane Doe')
            ->set('data.dob', '1990-01-15')
            ->call('submit')
            ->assertHasErrors('data.signature')
            ->set('data.signature', self::SIGNATURE)
            ->call('submit')
            ->assertHasErrors('data.agree');

        $this->assertSame(ConsentStatus::Pending, $groupConsent->refresh()->status);
    }

    public function test_submitted_group_consent_cannot_be_signed_again(): void
    {
        $groupConsent = GroupConsent::factory()->submitted()->create();

        Livewire::test(PublicGroupConsentForm::class, ['groupConsent' => $groupConsent])
            ->assertSet('step', 'submitted')
            ->assertSee('already been signed');
    }

    public function test_group_consent_pdf_renders_client_guardian_and_counsellor_details(): void
    {
        $groupConsent = GroupConsent::factory()->submitted()->create([
            'guardian_name' => 'John Doe',
            'guardian_relationship' => 'Father',
        ]);

        $html = view('pdf.group-consent', ['groupConsent' => $groupConsent])->render();

        $this->assertStringContainsString($groupConsent->full_name, $html);
        $this->assertStringContainsString('John Doe', $html);
        $this->assertStringContainsString('Joleen Poh Ee Bing', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
        $this->assertStringStartsWith('%PDF', Pdf::loadView('pdf.group-consent', ['groupConsent' => $groupConsent])->output());
    }

    public function test_group_consent_pdf_omits_guardian_section_when_empty(): void
    {
        $groupConsent = GroupConsent::factory()->submitted()->create();

        $html = view('pdf.group-consent', ['groupConsent' => $groupConsent])->render();

        $this->assertStringNotContainsString('Parent / Guardian', $html);
    }

    public function test_download_group_consent_pdf_action_is_only_visible_once_submitted(): void
    {
        $this->actingAs(User::factory()->create());
        $group = Group::factory()->create();
        [$pending, $signed] = Client::factory()->count(2)->create();
        $group->clients()->attach([$pending->id, $signed->id]);
        GroupConsent::factory()->for($pending)->for($group)->create();
        GroupConsent::factory()->for($signed)->for($group)->submitted()->create();

        Livewire::test(ClientsRelationManager::class, ['ownerRecord' => $group, 'pageClass' => ViewGroup::class])
            ->assertActionHidden(TestAction::make('downloadGroupConsentPdf')->table($pending))
            ->assertActionVisible(TestAction::make('downloadGroupConsentPdf')->table($signed))
            ->callAction(TestAction::make('downloadGroupConsentPdf')->table($signed))
            ->assertHasNoActionErrors();
    }
}
