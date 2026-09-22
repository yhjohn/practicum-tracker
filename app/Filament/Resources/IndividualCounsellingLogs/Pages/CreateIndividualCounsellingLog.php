<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Pages;

use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use Filament\Resources\Pages\CreateRecord;
use YousefAman\FilamentAutosave\HasAutosaveForCreate;

class CreateIndividualCounsellingLog extends CreateRecord
{
    use HasAutosaveForCreate;

    protected static string $resource = IndividualCounsellingLogResource::class;
}
