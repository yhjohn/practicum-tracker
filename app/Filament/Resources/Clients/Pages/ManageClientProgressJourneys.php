<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Exports\ProgressJourneyExport;
use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Resources\ProgressJourneys\ProgressJourneyResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ManageClientProgressJourneys extends ManageRelatedRecords
{
    protected static string $resource = ClientResource::class;

    protected static string $relationship = 'progressJourneys';

    protected static ?string $relatedResource = ProgressJourneyResource::class;

    public function getTitle(): string|Htmlable
    {
        return "Progress journey – {$this->getOwnerRecord()->name_with_code}";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportDocx')
                ->label('Export DOCX')
                ->icon(Phosphor::FileArrowDown)
                ->visible(fn (): bool => $this->getOwnerRecord()->progressJourneys()->exists())
                ->action(fn (): BinaryFileResponse => ProgressJourneyExport::download(
                    $this->getOwnerRecord()->newCollection([$this->getOwnerRecord()]),
                    'progress-journey-'.($this->getOwnerRecord()->client_code ?: $this->getOwnerRecord()->getKey()),
                )),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
