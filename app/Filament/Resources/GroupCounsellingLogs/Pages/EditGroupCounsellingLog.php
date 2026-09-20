<?php

namespace App\Filament\Resources\GroupCounsellingLogs\Pages;

use App\Filament\Resources\GroupCounsellingLogs\GroupCounsellingLogResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use YousefAman\FilamentAutosave\HasAutosave;

class EditGroupCounsellingLog extends EditRecord
{
    use HasAutosave;

    protected static string $resource = GroupCounsellingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
