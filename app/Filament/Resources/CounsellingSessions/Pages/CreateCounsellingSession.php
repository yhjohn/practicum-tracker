<?php

namespace App\Filament\Resources\CounsellingSessions\Pages;

use App\Enums\SessionType;
use App\Filament\Resources\CounsellingSessions\CounsellingSessionResource;
use App\Models\CounsellingSession;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateCounsellingSession extends CreateRecord
{
    protected static string $resource = CounsellingSessionResource::class;

    protected Width|string|null $maxContentWidth = Width::FourExtraLarge;

    /**
     * @var array<int>
     */
    protected array $attendeeIds = [];

    protected function fillForm(): void
    {
        $this->callHook('beforeFill');

        $this->form->fill(request()->only(['type', 'client_id', 'group_id']));

        $this->callHook('afterFill');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['sessionable_type'] = $data['type'] === SessionType::Individual ? 'client' : 'group';
        $data['sessionable_id'] = $data['type'] === SessionType::Individual ? ($data['client_id'] ?? null) : ($data['group_id'] ?? null);
        $data['duration_minutes'] = CounsellingSession::minutesBetween($data['start_time'] ?? null, $data['end_time'] ?? null)
            ?? CounsellingSession::parseDurationToMinutes($data['duration'] ?? null);

        $this->attendeeIds = $data['attendee_ids'] ?? [];

        unset($data['client_id'], $data['group_id'], $data['duration'], $data['attendee_ids']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->isGroup()) {
            $this->record->attendees()->sync(array_fill_keys($this->attendeeIds, ['attended' => true]));
        }
    }
}
