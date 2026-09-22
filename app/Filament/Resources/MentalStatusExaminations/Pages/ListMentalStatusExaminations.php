<?php

namespace App\Filament\Resources\MentalStatusExaminations\Pages;

use App\Filament\Resources\MentalStatusExaminations\MentalStatusExaminationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMentalStatusExaminations extends ListRecords
{
    protected static string $resource = MentalStatusExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
