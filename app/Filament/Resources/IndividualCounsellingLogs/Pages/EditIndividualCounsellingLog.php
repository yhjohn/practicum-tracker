<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Pages;

use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use YousefAman\FilamentAutosave\HasAutosave;

class EditIndividualCounsellingLog extends EditRecord
{
    use HasAutosave;

    protected static string $resource = IndividualCounsellingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
