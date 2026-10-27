<?php

namespace App\Filament\Resources\WebsiteLinks\Pages;

use App\Filament\Resources\WebsiteLinks\WebsiteLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWebsiteLink extends ViewRecord
{
    protected static string $resource = WebsiteLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
