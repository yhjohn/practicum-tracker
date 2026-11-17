<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Exports\ProgressJourneyExport;
use App\Filament\Resources\Clients\ClientResource;
use App\Models\Client;
use App\Models\ProgressJourney;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ListClients extends ListRecords
{
    protected static string $resource = ClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportProgressJourneys')
                ->label('Export all progress journeys')
                ->icon(Phosphor::FileArrowDown)
                ->visible(fn (): bool => ProgressJourney::exists())
                ->action(fn (): BinaryFileResponse => ProgressJourneyExport::download(
                    Client::whereHas('progressJourneys')->orderBy('id')->get(),
                    'progress-journeys-all',
                )),
            CreateAction::make(),
        ];
    }
}
