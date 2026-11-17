<?php

namespace App\Filament\Resources\Clients\Resources\ProgressJourneys\Pages;

use App\Filament\Resources\Clients\Resources\ProgressJourneys\ProgressJourneyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use YousefAman\FilamentAutosave\HasAutosave;

class EditProgressJourney extends EditRecord
{
    use HasAutosave;

    protected static string $resource = ProgressJourneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
