<?php

namespace App\Filament\Resources\CounsellingSessions\Pages;

use App\Filament\Resources\CounsellingSessions\CounsellingSessionResource;
use App\Filament\Resources\CounsellingSessions\Widgets\CounsellingSessionsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCounsellingSessions extends ListRecords
{
    protected static string $resource = CounsellingSessionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CounsellingSessionsOverview::class,
        ];
    }
}
