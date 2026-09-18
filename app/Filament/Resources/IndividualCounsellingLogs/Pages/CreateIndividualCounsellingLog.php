<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Pages;

use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use Filament\Resources\Pages\CreateRecord;
use YousefAman\FilamentAutosave\HasAutosaveForCreate;

class CreateIndividualCounsellingLog extends CreateRecord
{
    use HasAutosaveForCreate;

    protected static string $resource = IndividualCounsellingLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['signed_at'] = filled($data['signature'] ?? null) ? now() : null;

        return $data;
    }
}
