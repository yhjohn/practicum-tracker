<?php

namespace App\Filament\Resources\GroupCounsellingLogs\Pages;

use App\Filament\Resources\GroupCounsellingLogs\GroupCounsellingLogResource;
use Filament\Resources\Pages\CreateRecord;
use YousefAman\FilamentAutosave\HasAutosaveForCreate;

class CreateGroupCounsellingLog extends CreateRecord
{
    use HasAutosaveForCreate;

    protected static string $resource = GroupCounsellingLogResource::class;
}
