<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Pages;

use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use App\Filament\Resources\IndividualCounsellingLogs\Widgets\IndividualCounsellingLogsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIndividualCounsellingLogs extends ListRecords
{
    protected static string $resource = IndividualCounsellingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            IndividualCounsellingLogsOverview::class,
        ];
    }
}
