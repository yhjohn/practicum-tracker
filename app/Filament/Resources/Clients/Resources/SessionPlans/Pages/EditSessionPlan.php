<?php

namespace App\Filament\Resources\Clients\Resources\SessionPlans\Pages;

use App\Filament\Resources\Clients\Resources\SessionPlans\SessionPlanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use YousefAman\FilamentAutosave\HasAutosave;

class EditSessionPlan extends EditRecord
{
    use HasAutosave;

    protected static string $resource = SessionPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
