<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Enums\IntakeStatus;
use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\URL;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateIntakeLink')
                ->label('Generate intake link')
                ->icon(Phosphor::Link)
                ->modalHeading('Intake link')
                ->modalDescription('Share this link with the client. It expires in 7 days.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->fillForm(fn (Client $record): array => [
                    'url' => $this->intakeUrlFor($record),
                ])
                ->schema([
                    TextInput::make('url')
                        ->hiddenLabel()
                        ->readOnly()
                        ->copyable(),
                ]),
            EditAction::make(),
        ];
    }

    private function intakeUrlFor(Client $client): string
    {
        $intake = $client->intakes()
            ->where('status', IntakeStatus::Pending)
            ->latest()
            ->first()
            ?? $client->intakes()->create([
                'status' => IntakeStatus::Pending,
                'name' => $client->name,
                'email' => $client->email,
                'phone' => $client->phone,
                'dob' => $client->dob,
                'emergency_name' => $client->emergency_contact,
                'emergency_phone' => $client->emergency_contact_phone,
            ]);

        return URL::temporarySignedRoute('intake.show', now()->addDays(7), ['intake' => $intake->id]);
    }
}
