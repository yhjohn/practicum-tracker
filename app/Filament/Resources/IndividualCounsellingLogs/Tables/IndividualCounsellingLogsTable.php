<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Tables;

use App\Models\IndividualCounsellingLog;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class IndividualCounsellingLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('client.individualCounsellingLogs:id,client_id,session_number'))
            ->columns([
                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(),
                TextColumn::make('session_date')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('week')
                    ->formatStateUsing(fn (?int $state): string => $state === null ? '-' : "Week {$state}"),
                TextColumn::make('session_number')
                    ->label('Session no.')
                    ->placeholder('-'),
                TextColumn::make('total_sessions')
                    ->label('Total sessions')
                    ->tooltip('Reports logged for this client, and their session numbers')
                    ->state(fn (IndividualCounsellingLog $record): string => self::totalSessions($record->client?->individualCounsellingLogs)),
                TextColumn::make('place')
                    ->placeholder('-'),
                TextColumn::make('written_at')
                    ->label('Report written on')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Count of logged reports with their session numbers, e.g. "3 (1, 2, 4)", so gaps stand out.
     *
     * @param  Collection<int, IndividualCounsellingLog>|null  $logs
     */
    private static function totalSessions(?Collection $logs): string
    {
        if ($logs === null || $logs->isEmpty()) {
            return '-';
        }

        $numbers = $logs->pluck('session_number')->filter()->unique()->sort()->implode(', ');

        return $numbers === '' ? (string) $logs->count() : "{$logs->count()} ({$numbers})";
    }
}
