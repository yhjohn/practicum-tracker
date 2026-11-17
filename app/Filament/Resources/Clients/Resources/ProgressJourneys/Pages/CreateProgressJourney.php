<?php

namespace App\Filament\Resources\Clients\Resources\ProgressJourneys\Pages;

use App\Filament\Resources\Clients\Resources\ProgressJourneys\ProgressJourneyResource;
use Filament\Resources\Pages\CreateRecord;
use YousefAman\FilamentAutosave\HasAutosaveForCreate;

class CreateProgressJourney extends CreateRecord
{
    use HasAutosaveForCreate;

    protected static string $resource = ProgressJourneyResource::class;
}
