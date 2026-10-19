<?php

namespace Tests\Feature;

use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Models\Client;
use App\Models\Consent;
use App\Models\Intake;
use App\Models\IntakeV2;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientPdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_download_consent_pdf_action_is_hidden_until_consent_is_submitted(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        Consent::factory()->for($client)->create();

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->assertActionHidden('downloadConsentPdf');
    }

    public function test_download_consent_pdf_action_is_visible_and_callable_once_submitted(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        Consent::factory()->for($client)->submitted()->create();

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->assertActionVisible('downloadConsentPdf')
            ->callAction('downloadConsentPdf')
            ->assertHasNoActionErrors();
    }

    public function test_download_intake_pdf_action_is_hidden_until_an_intake_is_submitted(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        Intake::factory()->for($client)->create();

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->assertActionHidden('downloadIntakePdf');
    }

    public function test_download_intake_pdf_action_is_visible_and_callable_for_the_submitted_intake(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();
        Intake::factory()->for($client)->submitted()->create();

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->assertActionVisible('downloadIntakePdf')
            ->callAction('downloadIntakePdf')
            ->assertHasNoActionErrors();
    }

    public function test_consent_pdf_template_renders_the_signature_image(): void
    {
        $consent = Consent::factory()->submitted()->create();

        $pdf = Pdf::loadView('pdf.consent', ['consent' => $consent])->output();

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_consent_pdf_template_shows_the_client_name(): void
    {
        $consent = Consent::factory()->submitted()->create(['full_name' => 'Jane Client']);

        $html = view('pdf.consent', ['consent' => $consent])->render();

        $this->assertMatchesRegularExpression('/Client name<\/td>\s*<td>Jane Client<\/td>/', $html);
    }

    public function test_intake_pdf_template_renders_the_signature_image(): void
    {
        $intake = Intake::factory()->submitted()->create();

        $pdf = Pdf::loadView('pdf.intake', ['intake' => $intake])->output();

        $this->assertStringStartsWith('%PDF', $pdf);
    }

    public function test_intake_v2_pdf_template_renders_address_and_signature(): void
    {
        $intake = IntakeV2::factory()->submitted()->create(['address' => '1 Jalan Test']);

        $html = view('pdf.intake-v2', ['intake' => $intake])->render();

        $this->assertStringContainsString('1 Jalan Test', $html);
        $this->assertStringContainsString($intake->signature, $html);
        $this->assertStringStartsWith('%PDF', Pdf::loadView('pdf.intake-v2', ['intake' => $intake])->output());
    }
}
