<?php

namespace App\Filament\Resources\GroupCounsellingLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GroupCounsellingLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group.name')
                    ->label('Group')
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
                TextColumn::make('session_theme')
                    ->placeholder('-'),
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
}
