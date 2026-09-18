<?php

namespace Tests\Feature;

use App\Filament\Resources\IndividualCounsellingLogs\Pages\CreateIndividualCounsellingLog;
use App\Filament\Resources\IndividualCounsellingLogs\Pages\EditIndividualCounsellingLog;
use App\Filament\Resources\IndividualCounsellingLogs\Pages\ViewIndividualCounsellingLog;
use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndividualCounsellingLogTest extends TestCase
{
    use RefreshDatabase;

    private const SIGNATURE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';

    public function test_creating_a_log_stamps_signed_at_once_signed(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateIndividualCounsellingLog::class)
            ->fillForm([
                'client_id' => $client->id,
                'session_date' => '2026-09-18',
                'duration_hours' => 1.5,
                'place' => 'Main Campus Counselling Centre',
                'session_number' => 2,
                'part1_subjective_objective' => 'Client presented with...',
                'signature' => self::SIGNATURE,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $log = IndividualCounsellingLog::firstOrFail();

        $this->assertSame($client->id, $log->client_id);
        $this->assertSame('Main Campus Counselling Centre', $log->place);
        $this->assertSame(2, $log->session_number);
        $this->assertEquals(1.5, $log->duration_hours);
        $this->assertNotNull($log->signed_at);
    }

    public function test_creating_without_a_signature_leaves_it_unsigned(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        Livewire::test(CreateIndividualCounsellingLog::class)
            ->fillForm(['client_id' => $client->id])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNull(IndividualCounsellingLog::firstOrFail()->signed_at);
    }

    public function test_editing_preserves_the_original_signed_at_timestamp(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create();

        $log = IndividualCounsellingLog::create([
            'client_id' => $client->id,
            'place' => 'First save',
            'signature' => self::SIGNATURE,
            'signed_at' => now()->subDay(),
        ]);

        // Re-read from the database: the timestamp column truncates sub-second
        // precision, so comparing against the in-memory value would be off by a fraction.
        $originalSignedAt = $log->fresh()->signed_at;

        Livewire::test(EditIndividualCounsellingLog::class, ['record' => $log->getRouteKey()])
            ->fillForm(['place' => 'Second save', 'signature' => self::SIGNATURE])
            ->call('save')
            ->assertHasNoFormErrors();

        $log->refresh();

        $this->assertSame('Second save', $log->place);
        $this->assertTrue($log->signed_at->equalTo($originalSignedAt));
    }

    public function test_download_pdf_action_is_hidden_until_the_report_is_signed(): void
    {
        $this->actingAs(User::factory()->create());
        $log = IndividualCounsellingLog::create(['client_id' => Client::factory()->create()->id]);

        Livewire::test(ViewIndividualCounsellingLog::class, ['record' => $log->getRouteKey()])
            ->assertActionHidden('downloadPdf');
    }

    public function test_download_pdf_action_is_visible_and_callable_once_signed(): void
    {
        $this->actingAs(User::factory()->create());
        $log = IndividualCounsellingLog::create([
            'client_id' => Client::factory()->create()->id,
            'signature' => self::SIGNATURE,
            'signed_at' => now(),
        ]);

        Livewire::test(ViewIndividualCounsellingLog::class, ['record' => $log->getRouteKey()])
            ->assertActionVisible('downloadPdf')
            ->callAction('downloadPdf')
            ->assertHasNoActionErrors();
    }

    public function test_pdf_template_renders_the_signature_image(): void
    {
        $log = IndividualCounsellingLog::create([
            'client_id' => Client::factory()->create()->id,
            'signature' => self::SIGNATURE,
            'signed_at' => now(),
        ]);

        $pdf = Pdf::loadView('pdf.individual-counselling-log', ['log' => $log])->output();

        $this->assertStringStartsWith('%PDF', $pdf);
    }
}
