<?php

namespace App\Filament\Resources\CounsellingSessions\Schemas;

use App\Enums\SessionType;
use App\Models\Client;
use App\Models\CounsellingSession;
use App\Models\Group;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class CounsellingSessionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([

                    Select::make('type')
                        ->options(SessionType::class)
                        ->live()
                        ->required(),
                    Select::make('client_id')
                        ->label('Client')
                        ->options(Client::query()->pluck('name', 'id'))
                        ->searchable()
                        ->visible(fn (Get $get): bool => $get('type') === SessionType::Individual)
                        ->required(fn (Get $get): bool => $get('type') === SessionType::Individual),
                    Select::make('group_id')
                        ->label('Group')
                        ->options(Group::query()->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->visible(fn (Get $get): bool => $get('type') === SessionType::Group)
                        ->required(fn (Get $get): bool => $get('type') === SessionType::Group),
                    CheckboxList::make('attendee_ids')
                        ->label('Attendance')
                        ->options(fn (Get $get) => Group::find($get('group_id'))?->clients()->pluck('name', 'clients.id') ?? [])
                        ->bulkToggleable()
                        ->visible(fn (Get $get): bool => $get('type') === SessionType::Group && filled($get('group_id')))
                        ->columnSpanFull(),
                ])->columnSpanFull(),
                TextInput::make('week')
                    ->label('Week no.'),
                DatePicker::make('session_date')
                    ->required(),
                TimePicker::make('start_time')
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        self::updateDuration($get, $set);
                    })
                    ->default(null),
                TimePicker::make('end_time')
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        self::updateDuration($get, $set);
                    })
                    ->default(null),
                TextInput::make('duration')
                    ->label('Duration')
                    ->helperText('Calculated from start/end time, or type e.g. "1h 15m", "1h", "45m"')
                    ->rule('regex:/^(\d+\s*h)?\s*(\d+\s*m)?$/i')
                    ->default(null),
                RichEditor::make('session_notes')
                    ->default(null)
                    ->columnSpanFull(),
                FileUpload::make('attachments')
                    ->multiple()
                    ->directory('counselling-sessions')
                    ->visibility('private')
                    ->default(null),
            ]);
    }

    protected static function updateDuration(Get $get, Set $set): void
    {
        $minutes = CounsellingSession::minutesBetween($get('start_time'), $get('end_time'));

        if ($minutes !== null) {
            $set('duration', CounsellingSession::formatMinutesToDuration($minutes));
        }
    }
}
