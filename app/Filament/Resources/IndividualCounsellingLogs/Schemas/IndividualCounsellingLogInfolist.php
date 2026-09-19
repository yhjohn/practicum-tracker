<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class IndividualCounsellingLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('client.name')
                    ->label('Client'),
                TextEntry::make('session_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('start_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('duration_hours')
                    ->label('Duration (hours)')
                    ->placeholder('-'),
                TextEntry::make('place')
                    ->placeholder('-'),
                TextEntry::make('session_number')
                    ->label('Session no.')
                    ->placeholder('-'),

                TextEntry::make('part1_subjective_objective')
                    ->label('Part 1: Issues / Problems Presented')
                    ->html()
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('part2_assessment')
                    ->label('Part 2: Assessment of Issues / Problems')
                    ->html()
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('part3_treatment_plan')
                    ->label('Part 3: Treatment Planning and Action')
                    ->html()
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('part4_evaluation')
                    ->label('Part 4: Evaluation of Counseling Session')
                    ->html()
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('written_at')
                    ->label('Report written on')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
