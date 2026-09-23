<?php

namespace App\Filament\Resources\MentalStatusExaminations\Pages;

use App\Filament\Resources\MentalStatusExaminations\MentalStatusExaminationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMentalStatusExamination extends EditRecord
{
    protected static string $resource = MentalStatusExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
