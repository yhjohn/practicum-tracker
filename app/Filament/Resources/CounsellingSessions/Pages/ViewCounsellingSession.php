<?php

namespace App\Filament\Resources\CounsellingSessions\Pages;

use App\Filament\Resources\CounsellingSessions\CounsellingSessionResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewCounsellingSession extends ViewRecord
{
    protected static string $resource = CounsellingSessionResource::class;

    protected Width|string|null $maxContentWidth = Width::FourExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
