<?php

namespace App\Filament\Resources\Clients\Resources\ProgressJourneys\Schemas;

use App\Models\IndividualCounsellingLog;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;

class ProgressJourneyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('individual_counselling_log_id')
                    ->label('Session')
                    ->options(fn (Page $livewire): array => $livewire->getParentRecord()
                        ->individualCounsellingLogs()
                        ->orderBy('session_number')
                        ->orderBy('session_date')
                        ->get()
                        ->mapWithKeys(fn (IndividualCounsellingLog $log): array => [
                            $log->id => "Session {$log->session_number}".($log->session_date ? ' – '.$log->session_date->format('j M Y') : ''),
                        ])
                        ->all())
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->validationMessages(['unique' => 'This session already has a progress journey entry.']),
                RichEditor::make('background')
                    ->label('Background of the presenting issues')
                    ->columnSpanFull(),
                RichEditor::make('case_conceptualisation')
                    ->label('Case conceptualisation')
                    ->columnSpanFull(),
                RichEditor::make('interventions')
                    ->columnSpanFull(),
                RichEditor::make('progress_summary')
                    ->label('Client progress summary')
                    ->columnSpanFull(),
            ]);
    }
}
