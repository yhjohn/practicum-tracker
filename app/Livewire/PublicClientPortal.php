<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\IntakeV2;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class PublicClientPortal extends Component
{
    public ?string $token = null;

    public ?string $codeInput = null;

    public string $step = 'code';

    public ?Client $client = null;

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->client = Client::where('public_access_token', $token)->firstOrFail();

        if ($this->client->public_access_expires_at && now()->gt($this->client->public_access_expires_at)) {
            $this->step = 'expired';

            return;
        }

        if ($this->isVerified()) {
            $this->step = 'portal';
        }
    }

    public function verifyAccessCode(): void
    {
        $this->validate(['codeInput' => ['required', 'string']]);

        if (! $this->client || ! $this->client->public_access_code || ! Hash::check($this->codeInput, $this->client->public_access_code)) {
            $this->addError('codeInput', 'That code is incorrect.');

            return;
        }

        session()->put($this->sessionKey(), now()->addDays(2));
        $this->codeInput = null;
        $this->step = 'portal';

        Notification::make()
            ->title('Access granted')
            ->success()
            ->send();
    }

    public function startOver(): void
    {
        session()->forget($this->sessionKey());
        $this->step = 'code';
        $this->codeInput = null;
    }

    public function render(): View
    {
        $intake = null;
        $consent = null;

        if ($this->client) {
            $intake = $this->client->currentIntake();

            $consent = $this->client->consent ?? $this->client->consent()->create([
                'full_name' => $this->client->name,
            ]);
        }

        $intakeRouteName = $intake instanceof IntakeV2 ? 'intake-v2.show' : 'intake.show';

        return view('livewire.public-client-portal', [
            'intake' => $intake,
            'consent' => $consent,
            'intakeUrl' => $intake ? URL::temporarySignedRoute($intakeRouteName, now()->addDays(30), ['intake' => $intake->id]) : null,
            'consentUrl' => $consent ? URL::temporarySignedRoute('consent.show', now()->addDays(30), ['consent' => $consent->id]) : null,
        ]);
    }

    private function isVerified(): bool
    {
        if (! $this->client) {
            return false;
        }

        $verifiedUntil = session($this->sessionKey());

        return $verifiedUntil && now()->lt($verifiedUntil);
    }

    private function sessionKey(): string
    {
        return "client_portal.{$this->client->id}";
    }
}
