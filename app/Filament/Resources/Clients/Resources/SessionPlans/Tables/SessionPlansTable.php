<?php

namespace App\Filament\Resources\Clients\Resources\SessionPlans\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SessionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('session_number')
            ->columns([
                TextColumn::make('session_number')
                    ->label('Session no.')
                    ->sortable(),
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
