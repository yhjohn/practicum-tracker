<?php

namespace App\Filament\Resources\LogBookEntries;

use App\Enums\LogBookActivity;
use App\Filament\Resources\GroupCounsellingLogs\GroupCounsellingLogResource;
use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use App\Filament\Resources\LogBookEntries\Pages\ManageLogBookEntries;
use App\Models\IndividualCounsellingLog;
use App\Models\LogBookEntry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class LogBookEntryResource extends Resource
{
    protected static ?string $model = LogBookEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Notebook;

    protected static ?string $modelLabel = 'log book entry';

    protected static ?string $navigationLabel = 'Log Book';

    protected static ?string $pluralModelLabel = 'Log Book';

    /**
     * @return array<int, string>
     */
    public static function weekOptions(): array
    {
        return collect(range(1, config('practicum.weeks')))
            ->mapWithKeys(fn (int $week): array => [$week => "Week {$week}"])
            ->all();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('week')
                    ->options(static::weekOptions())
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(fn (Select $component, ?LogBookEntry $record) => $component->state($record?->week))
                    ->afterStateUpdated(fn (Set $set, ?string $state) => filled($state)
                        ? $set('session_date', LogBookEntry::weekStart((int) $state)->toDateString())
                        : null),
                DatePicker::make('session_date')
                    ->label('Date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                        'week',
                        filled($state) ? (new LogBookEntry(['session_date' => $state]))->week : null,
                    )),
                Select::make('activity')
                    ->options(LogBookActivity::manualOptions())
                    ->required(),
                TextInput::make('duration_hours')
                    ->label('Time spent (hours)')
                    ->numeric()
                    ->step(0.25)
                    ->minValue(0.25)
                    ->required(),
                TimePicker::make('start_time')
                    ->seconds(false)
                    ->label('Start time'),
                TextInput::make('description')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->label('Notes / Observations')
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('session_date', 'desc')
            ->columns([
                TextColumn::make('week')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : "Week {$state}"),
                TextColumn::make('session_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('time_range')
                    ->label('Time')
                    ->state(fn (LogBookEntry $record): ?string => $record->timeRange())
                    ->placeholder('-'),
                TextColumn::make('description')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('activity')
                    ->badge(),
                TextColumn::make('client_code')
                    ->label('Client / Group')
                    ->placeholder('-'),
                TextColumn::make('number_of_clients')
                    ->label('No. of clients'),
                TextColumn::make('duration_hours')
                    ->label('Duration (hrs)')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total hours')),
            ])
            ->filters([
                SelectFilter::make('week')
                    ->options(static::weekOptions())
                    ->query(fn (Builder $query, array $data): Builder => filled($data['value'])
                        ? $query->whereBetween('session_date', [
                            LogBookEntry::weekStart((int) $data['value'])->toDateString(),
                            LogBookEntry::weekStart((int) $data['value'])->addDays(6)->toDateString(),
                        ])
                        : $query),
                SelectFilter::make('activity')
                    ->options(LogBookActivity::class),
            ])
            ->recordActions([
                Action::make('viewLog')
                    ->label('View log')
                    ->icon(Phosphor::ArrowSquareOut)
                    ->url(fn (LogBookEntry $record): string => $record->loggable instanceof IndividualCounsellingLog
                        ? IndividualCounsellingLogResource::getUrl('view', ['record' => $record->loggable])
                        : GroupCounsellingLogResource::getUrl('view', ['record' => $record->loggable]))
                    ->hidden(fn (LogBookEntry $record): bool => $record->isManual()),
                EditAction::make()
                    ->visible(fn (LogBookEntry $record): bool => $record->isManual()),
                DeleteAction::make()
                    ->visible(fn (LogBookEntry $record): bool => $record->isManual()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLogBookEntries::route('/'),
        ];
    }
}
