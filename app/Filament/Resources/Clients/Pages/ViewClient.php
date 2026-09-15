<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Enums\ConsentStatus;
use App\Enums\IntakeStatus;
use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use App\Models\Consent;
use App\Models\Intake;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ViewClient extends ViewRecord
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generateClientPortalLink')
                ->label('Generate client portal link')
                ->icon(Phosphor::Link)
                ->modalHeading('Client portal')
                ->modalDescription('Share the portal link and passcode with the client. The portal and its links remain valid for 30 days.')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->fillForm(function (Client $record): array {
                    $record->public_access_token = $record->public_access_token ?: (string) Str::uuid();
                    $record->public_access_expires_at = $record->public_access_expires_at ?: now()->addDays(30);
                    $code = $this->regeneratePortalAccessCode($record);

                    return [
                        'url' => URL::temporarySignedRoute('client.portal', $record->public_access_expires_at, ['token' => $record->public_access_token]),
                        'code' => $code,
                    ];
                })
                ->schema([
                    TextInput::make('url')
                        ->label('Portal link')
                        ->readOnly()
                        ->copyable(),
                    TextInput::make('code')
                        ->label('Passcode')
                        ->readOnly()
                        ->copyable(),
                ]),
            Action::make('downloadConsentPdf')
                ->label('Download consent PDF')
                ->icon(Phosphor::FileArrowDown)
                ->visible(fn (Client $record): bool => $record->consent?->status === ConsentStatus::Submitted)
                ->action(fn (Client $record) => response()->streamDownload(
                    fn () => print (Pdf::loadView('pdf.consent', ['consent' => $record->consent])->output()),
                    "consent-{$record->name}.pdf",
                )),
            Action::make('downloadIntakePdf')
                ->label('Download intake PDF')
                ->icon(Phosphor::FileArrowDown)
                ->visible(fn (Client $record): bool => $record->intake?->status === IntakeStatus::Submitted)
                ->action(fn (Client $record) => response()->streamDownload(
                    fn () => print (Pdf::loadView('pdf.intake', ['intake' => $record->intake])->output()),
                    "intake-{$record->intake->name}-{$record->intake->id}.pdf",
                )),
            EditAction::make(),
        ];
    }

    private function pendingIntakeFor(Client $client): Intake
    {
        return $client->intake ?? $client->intake()->create([
            'status' => IntakeStatus::Pending,
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
            'dob' => $client->dob,
            'emergency_name' => $client->emergency_contact,
            'emergency_phone' => $client->emergency_contact_phone,
        ]);
    }

    private function pendingConsentFor(Client $client): Consent
    {
        return $client->consent ?? $client->consent()->create([
            'full_name' => $client->name,
        ]);
    }

    private function regenerateAccessCode(Intake $intake): string
    {
        $code = (string) random_int(100000, 999999);

        $intake->update(['access_code' => Hash::make($code)]);

        return $code;
    }

    private function regeneratePortalAccessCode(Client $client): string
    {
        $code = (string) random_int(100000, 999999);

        $client->public_access_code = Hash::make($code);
        $client->public_access_expires_at = now()->addDays(30);
        $client->save();

        return $code;
    }
}
