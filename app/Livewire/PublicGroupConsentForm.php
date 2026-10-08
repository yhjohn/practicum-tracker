<?php

namespace App\Livewire;

use App\Enums\ConsentStatus;
use App\Filament\Schemas\GroupConsents\GroupConsentForm;
use App\Models\GroupConsent;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class PublicGroupConsentForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public GroupConsent $groupConsent;

    public string $step = 'form';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(GroupConsent $groupConsent): void
    {
        $this->groupConsent = $groupConsent;

        if ($this->groupConsent->status === ConsentStatus::Submitted) {
            $this->step = 'submitted';

            return;
        }

        $this->form->fill($this->groupConsent->only([
            'full_name', 'dob', 'agree', 'signature', 'guardian_name', 'guardian_relationship', 'guardian_signature',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(GroupConsentForm::schema())
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (($data['agree'] ?? false) !== true) {
            $this->addError('data.agree', 'Please tick the acknowledgement.');

            return;
        }

        $this->groupConsent->fill($data);
        $this->groupConsent->status = ConsentStatus::Submitted;
        $this->groupConsent->signature_date = now();
        $this->groupConsent->submitted_at = now();
        $this->groupConsent->save();

        $this->step = 'submitted';

        Notification::make()
            ->title('Thank you, your group consent has been signed and submitted.')
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.public-group-consent-form');
    }
}
