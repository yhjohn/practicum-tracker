<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IndividualCounsellingLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(),
                TextColumn::make('session_date')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('session_number')
                    ->label('Session no.')
                    ->placeholder('-'),
                TextColumn::make('place')
                    ->placeholder('-'),
                TextColumn::make('signed_at')
                    ->label('Signed')
                    ->dateTime()
                    ->placeholder('Draft')
                    ->sortable(),
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
