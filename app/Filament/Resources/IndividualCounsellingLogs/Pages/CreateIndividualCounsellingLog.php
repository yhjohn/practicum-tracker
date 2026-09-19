<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Pages;

use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use App\Models\IndividualCounsellingLog;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use YousefAman\FilamentAutosave\HasAutosaveForCreate;

class CreateIndividualCounsellingLog extends CreateRecord
{
    use HasAutosaveForCreate;

    protected static string $resource = IndividualCounsellingLogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['session_date'] ?? null) && filled($data['start_time'] ?? null)) {
            $sessionStart = Carbon::parse($data['session_date'].' '.$data['start_time']);

            $data['written_at'] = IndividualCounsellingLog::randomWrittenAt($sessionStart);
        }

        return $data;
    }
}
