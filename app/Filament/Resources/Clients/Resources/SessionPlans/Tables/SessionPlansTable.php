<?php

namespace App\Filament\Resources\Clients\Resources\SessionPlans\Tables;

use App\Models\SessionPlan;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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
                TextColumn::make('attachments')
                    ->state(fn (SessionPlan $record): string => collect($record->attachments)
                        ->map(fn (string $path): string => '<a href="'.e(Storage::disk(config('filament.default_filesystem_disk'))->temporaryUrl($path, now()->addMinutes(10))).'" target="_blank" class="text-primary-600 underline">'.e(basename($path)).'</a>')
                        ->implode('<br>'))
                    ->html()
                    ->placeholder('-')
                    ->disabledClick(),
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
