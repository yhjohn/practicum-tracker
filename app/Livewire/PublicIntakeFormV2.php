<?php

namespace App\Livewire;

use App\Enums\IntakeStatus;
use App\Filament\Schemas\Intakes\IntakeWizardV2;
use App\Models\IntakeV2;
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
class PublicIntakeFormV2 extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    /** How long a verified session may stay on the form without re-verifying. */
    protected const SESSION_TIMEOUT_MINUTES = 30;

    public IntakeV2 $intake;

    public string $step = 'code';

    public ?string $codeInput = null;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(IntakeV2 $intake): void
    {
        $this->intake = $intake;

        if ($this->intake->status === IntakeStatus::Submitted) {
            $this->step = 'submitted';

            return;
        }

        if (! $this->isVerified()) {
            $this->step = 'code';

            return;
        }

        $this->step = 'form';
        $this->form->fill($this->intakeAttributes());
    }

    public function verifyAccessCode(): void
    {
        $this->validate(['codeInput' => ['required', 'string']]);

        $rateLimitKey = "intake-v2-access-code:{$this->intake->id}";

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
                Wizard::make(IntakeWizardV2::steps($this))
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
        return "client_portal.{$this->intake->client_id}";
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
            'name', 'age', 'phone', 'email', 'address', 'occupation', 'sex',
            'emergency_name', 'emergency_relationship', 'emergency_phone',
            'marital',
            'education_level', 'education_level_other', 'job_satisfaction',
            'enjoyable_activities', 'enjoyable_activities_other',
            'personal_strengths', 'personal_strengths_other',
            'coping_resources', 'coping_resources_other',
            'strengths_to_build',
            'family_relationships', 'family_relationships_other',
            'childhood_experiences', 'childhood_experiences_other',
            'family_support_received', 'background_notes',
            'beliefs', 'beliefs_other', 'spiritual_upbringing_role',
            'substances_used', 'substances_used_other',
            'substance_use_frequency', 'substance_use_frequency_other',
            'previous_counselling', 'previous_counselling_when',
            'mental_health_diagnosis', 'mental_health_diagnosis_details',
            'psychiatric_services', 'previous_support_feedback',
            'mood_recent', 'mood_recent_other',
            'harm_thoughts', 'harm_thoughts_response',
            'current_stressors', 'current_stressors_other', 'current_stressors_impact',
            'main_concern', 'counselling_goals', 'counselling_goals_other',
            'anything_else', 'signature',
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
            'phone' => $client->phone ?: ($data['phone'] ?? null),
            'email' => $client->email ?: ($data['email'] ?? null),
            'emergency_contact' => $client->emergency_contact ?: ($data['emergency_name'] ?? null),
            'emergency_contact_phone' => $client->emergency_contact_phone ?: ($data['emergency_phone'] ?? null),
        ], fn (mixed $value): bool => $value !== null));

        $client->save();
    }

    public function render(): View
    {
        return view('livewire.public-intake-form-v2');
    }
}
