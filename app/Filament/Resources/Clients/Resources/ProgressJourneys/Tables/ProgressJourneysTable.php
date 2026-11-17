<?php

namespace App\Filament\Resources\Clients\Resources\ProgressJourneys\Tables;

use App\Models\IndividualCounsellingLog;
use App\Models\ProgressJourney;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProgressJourneysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('individualCounsellingLog'))
            ->defaultSort(fn (Builder $query): Builder => $query->orderBy(
                IndividualCounsellingLog::select('session_number')->whereColumn('individual_counselling_logs.id', 'progress_journeys.individual_counselling_log_id'),
            ))
            ->columns([
                TextColumn::make('individualCounsellingLog.session_number')
                    ->label('Session no.'),
                TextColumn::make('individualCounsellingLog.session_date')
                    ->label('Session date')
                    ->date(),
                IconColumn::make('ai_draft')
                    ->label('AI draft')
                    ->state(fn (ProgressJourney $record): bool => $record->isUntouchedAiDraft())
                    ->boolean()
                    ->tooltip(fn (bool $state): string => $state
                        ? 'Drafted by Claude and not edited yet; redrafted when the session notes change.'
                        : 'Written or edited by you; Claude will not overwrite it.'),
                TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
