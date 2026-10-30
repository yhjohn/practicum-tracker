<?php

namespace Tests\Feature;

use App\Filament\Resources\Clients\Pages\ViewClient;
use App\Models\Client;
use App\Models\Consent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class OpenClientPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_opens_client_portal_without_passcode(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create(['public_access_token' => null, 'public_access_expires_at' => null]);

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->callAction('openClientPortal')
            ->assertRedirectContains('/portal/');

        $client->refresh();
        $this->assertNotNull($client->public_access_token);
        $this->assertTrue($client->public_access_expires_at->isFuture());
        $this->assertTrue(now()->lt(session("client_portal.{$client->id}")));

        $consent = Consent::factory()->for($client)->create();

        $this->get(URL::temporarySignedRoute('consent.show', now()->addDay(), ['consent' => $consent->id]))
            ->assertOk();
    }

    public function test_expired_portal_access_is_extended(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::factory()->create(['public_access_token' => 'token', 'public_access_expires_at' => now()->subDay()]);

        Livewire::test(ViewClient::class, ['record' => $client->getRouteKey()])
            ->callAction('openClientPortal');

        $this->assertTrue($client->refresh()->public_access_expires_at->isFuture());
    }
}
