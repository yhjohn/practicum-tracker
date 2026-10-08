<?php

namespace App\Filament\Resources\Groups\RelationManagers;

use App\Enums\ConsentStatus;
use App\Models\Client;
use App\Models\GroupConsent;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ClientsRelationManager extends RelationManager
{
    protected static string $relationship = 'clients';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('groupConsents'))
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('group_consent_status')
                    ->label('Group consent')
                    ->badge()
                    ->state(fn (Client $record): ConsentStatus => $this->groupConsentFor($record)?->status ?? ConsentStatus::Pending),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Add client to group'),
            ])
            ->recordActions([
                Action::make('downloadGroupConsentPdf')
                    ->label('Consent PDF')
                    ->icon(Phosphor::FileArrowDown)
                    ->visible(fn (Client $record): bool => $this->groupConsentFor($record)?->status === ConsentStatus::Submitted)
                    ->action(fn (Client $record) => response()->streamDownload(
                        fn () => print (Pdf::loadView('pdf.group-consent', ['groupConsent' => $this->groupConsentFor($record)])->output()),
                        "group-consent-{$this->getOwnerRecord()->name}-{$record->name}.pdf",
                    )),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }

    private function groupConsentFor(Client $client): ?GroupConsent
    {
        return $client->groupConsents->firstWhere('group_id', $this->getOwnerRecord()->getKey());
    }
}
