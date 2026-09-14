<?php

namespace App\Livewire;

use App\Enums\ConsentStatus;
use App\Filament\Schemas\Consents\ConsentForm;
use App\Models\Consent;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class PublicConsentForm extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public Consent $consent;

    public string $step = 'form';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(Consent $consent): void
    {
        $this->consent = $consent;

        if ($this->consent->status === ConsentStatus::Submitted) {
            $this->step = 'submitted';

            return;
        }

        $this->form->fill($this->consent->only(['full_name', 'agree', 'signature']));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(ConsentForm::schema($this))
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        if (($data['agree'] ?? false) !== true) {
            $this->addError('data.agree', 'Please tick the acknowledgement.');

            return;
        }

        $this->consent->fill($data);
        $this->consent->status = ConsentStatus::Submitted;
        $this->consent->signature_date = now();
        $this->consent->submitted_at = now();
        $this->consent->save();

        $this->step = 'submitted';

        Notification::make()
            ->title('Thank you, your consent has been signed and submitted.')
            ->success()
            ->send();
    }

    public function render(): View
    {
        return view('livewire.public-consent-form');
    }
}
