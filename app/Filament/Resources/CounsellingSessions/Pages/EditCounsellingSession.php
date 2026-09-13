<?php

namespace App\Filament\Resources\CounsellingSessions\Pages;

use App\Enums\SessionType;
use App\Filament\Resources\CounsellingSessions\CounsellingSessionResource;
use App\Models\CounsellingSession;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditCounsellingSession extends EditRecord
{
    protected static string $resource = CounsellingSessionResource::class;

    protected Width | string | null $maxContentWidth = Width::FourExtraLarge;

    /**
     * @var array<int>
     */
    protected array $attendeeIds = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['client_id'] = $data['sessionable_type'] === 'client' ? $data['sessionable_id'] : null;
        $data['group_id'] = $data['sessionable_type'] === 'group' ? $data['sessionable_id'] : null;
        $data['duration'] = CounsellingSession::formatMinutesToDuration($data['duration_minutes'] ?? null);
        $data['attendee_ids'] = $this->record->attendees()->pluck('clients.id')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['sessionable_type'] = $data['type'] === SessionType::Individual ? 'client' : 'group';
        $data['sessionable_id'] = $data['type'] === SessionType::Individual ? ($data['client_id'] ?? null) : ($data['group_id'] ?? null);
        $data['duration_minutes'] = CounsellingSession::minutesBetween($data['start_time'] ?? null, $data['end_time'] ?? null)
            ?? CounsellingSession::parseDurationToMinutes($data['duration'] ?? null);

        $this->attendeeIds = $data['attendee_ids'] ?? [];

        unset($data['client_id'], $data['group_id'], $data['duration'], $data['attendee_ids']);

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->record->isGroup()) {
            $this->record->attendees()->sync(array_fill_keys($this->attendeeIds, ['attended' => true]));
        } else {
            $this->record->attendees()->sync([]);
        }
    }
}
