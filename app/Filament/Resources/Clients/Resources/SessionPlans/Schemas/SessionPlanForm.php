<?php

namespace App\Filament\Resources\Clients\Resources\SessionPlans\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class SessionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('session_number')
                    ->label('Session no.')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(255)
                    ->required()
                    ->default(fn (Page $livewire): int => $livewire->getParentRecord()->sessionPlans()->max('session_number') + 1)
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Page $livewire): Unique => $rule->where('client_id', $livewire->getParentRecord()->getKey())),
                RichEditor::make('plan')
                    ->columnSpanFull(),
            ]);
    }
}
