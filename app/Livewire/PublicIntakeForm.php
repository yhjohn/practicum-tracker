<?php

namespace App\Livewire;

use App\Enums\IntakeStatus;
use App\Filament\Schemas\Intakes\IntakeWizard;
use App\Models\Intake;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class PublicIntakeForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    /** How long a verified session may stay on the form without re-verifying. */
    protected const SESSION_TIMEOUT_MINUTES = 30;

    public Intake $intake;

    public string $step = 'code';

    public ?string $codeInput = null;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(Intake $intake): void
    {
        $this->intake = $intake;

        if ($this->intake->status === IntakeStatus::Submitted) {
            $this->step = 'submitted';

            return;
        }

        if ($this->isVerified()) {
            $this->step = 'form';
            $this->form->fill($this->intakeAttributes());
        }
    }

    public function verifyAccessCode(): void
    {
        $this->validate(['codeInput' => ['required', 'string']]);

        $rateLimitKey = "intake-access-code:{$this->intake->id}";

        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 5)) {
            $this->addError('codeInput', 'Too many attempts. Please contact your counsellor for a new code.');

            return;
        }

        RateLimiter::hit($rateLimitKey, decaySeconds: 900);

        if (! $this->intake->access_code || ! Hash::check($this->codeInput, $this->intake->access_code)) {
            $this->addError('codeInput', 'That code is incorrect.');

            return;
        }

        RateLimiter::clear($rateLimitKey);

        session([$this->sessionKey() => now()->addMinutes(self::SESSION_TIMEOUT_MINUTES)]);

        $this->codeInput = null;
        $this->step = 'form';
        $this->form->fill($this->intakeAttributes());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make(IntakeWizard::steps($this))
                    ->skippable()
                    ->submitAction(new HtmlString(
                        '<button type="submit" class="fi-btn fi-btn-size-md fi-color-primary fi-btn-color-primary rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-500">Submit</button>'
                    )),
            ])
            ->statePath('data');
    }

    public function saveProgress(): void
    {
        $this->intake->fill($this->data ?? []);
        $this->intake->save();

        if (! $this->isVerified()) {
            $this->step = 'code';

            Notification::make()
                ->title('Your session expired')
                ->body('Your progress was saved. Please enter your access code again to continue.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Progress saved')
            ->success()
            ->send();
    }

    public function submit(): void
    {
        if (! $this->isVerified()) {
            $this->intake->fill($this->data ?? []);
            $this->intake->save();

            $this->step = 'code';

            Notification::make()
                ->title('Your session expired')
                ->body('Your progress was saved. Please enter your access code again to finish submitting.')
                ->warning()
                ->send();

            return;
        }

        $data = $this->form->getState();

        $this->intake->fill($data);
        $this->intake->status = IntakeStatus::Submitted;
        $this->intake->signature_date = now();
        $this->intake->submitted_at = now();
        $this->intake->save();

        $this->backfillClient($data);

        session()->forget($this->sessionKey());
        $this->step = 'submitted';

        Notification::make()
            ->title('Thank you, your intake form has been submitted.')
            ->success()
            ->send();
    }

    private function sessionKey(): string
    {
        return "intakes.{$this->intake->id}.verified_until";
    }

    private function isVerified(): bool
    {
        $verifiedUntil = session($this->sessionKey());

        return $verifiedUntil && now()->lt($verifiedUntil);
    }

    /**
     * @return array<string, mixed>
     */
    private function intakeAttributes(): array
    {
        return $this->intake->only([
            'name', 'dob', 'age', 'phone', 'email', 'occupation', 'sex',
            'emergency_name', 'emergency_relationship', 'emergency_phone',
            'marital', 'relationship_length', 'marriages',
            'education', 'education_current', 'education_details',
            'job_title', 'employment_history', 'job_satisfaction',
            'strength_activities', 'strengths', 'others_strengths', 'supports',
            'trauma', 'trauma_details',
            'family_description', 'childhood_experience', 'family_support',
            'counselling_history', 'diagnosis', 'therapy_experience', 'medical_history',
            'substance_use', 'substance_treatment',
            'legal_issues', 'legal_impact',
            'emotions', 'safety', 'support_system', 'current_stress', 'memory',
            'belief_system', 'spiritual_upbringing',
            'seeking_reason', 'first_occurred', 'main_concern', 'family_concern', 'counselling_outcome', 'anything_else',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function backfillClient(array $data): void
    {
        $client = $this->intake->client;

        $client->fill(array_filter([
            'name' => $client->name ?: ($data['name'] ?? null),
            'dob' => $client->dob ?: ($data['dob'] ?? null),
            'phone' => $client->phone ?: ($data['phone'] ?? null),
            'email' => $client->email ?: ($data['email'] ?? null),
            'emergency_contact' => $client->emergency_contact ?: ($data['emergency_name'] ?? null),
            'emergency_contact_phone' => $client->emergency_contact_phone ?: ($data['emergency_phone'] ?? null),
        ], fn (mixed $value): bool => $value !== null));

        $client->save();
    }

    public function render(): View
    {
        return view('livewire.public-intake-form');
    }
}
