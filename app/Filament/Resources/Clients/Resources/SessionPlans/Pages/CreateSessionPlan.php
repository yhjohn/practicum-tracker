<?php

namespace App\Filament\Resources\Clients\Resources\SessionPlans\Pages;

use App\Filament\Resources\Clients\Resources\SessionPlans\SessionPlanResource;
use Filament\Resources\Pages\CreateRecord;
use YousefAman\FilamentAutosave\HasAutosaveForCreate;

class CreateSessionPlan extends CreateRecord
{
    use HasAutosaveForCreate;

    protected static string $resource = SessionPlanResource::class;
}
