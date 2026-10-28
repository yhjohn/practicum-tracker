<?php

namespace App\Filament\Resources\WebsiteLinks\Pages;

use App\Filament\Resources\WebsiteLinks\WebsiteLinkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWebsiteLinks extends ListRecords
{
    protected static string $resource = WebsiteLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
