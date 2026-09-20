<?php

namespace App\Filament\Resources\GroupCounsellingLogs\Pages;

use App\Filament\Resources\GroupCounsellingLogs\GroupCounsellingLogResource;
use App\Filament\Resources\GroupCounsellingLogs\Widgets\GroupCounsellingLogsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGroupCounsellingLogs extends ListRecords
{
    protected static string $resource = GroupCounsellingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            GroupCounsellingLogsOverview::class,
        ];
    }
}
