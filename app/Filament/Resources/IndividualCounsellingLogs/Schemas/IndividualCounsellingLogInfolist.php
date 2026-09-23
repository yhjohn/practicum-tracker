<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Schemas;

use App\Models\IndividualCounsellingLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class IndividualCounsellingLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('')
                    ->schema([
                        TextEntry::make('client.name')
                            ->label('Client'),
                        TextEntry::make('session_date')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('week')
                            ->formatStateUsing(fn (?int $state): ?string => $state === null ? null : "Week {$state}")
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
                    ])
                    ->extraAttributes(['class' => 'invert-section'])
                    ->columns(4)
                    ->columnSpanFull(),
                Section::make('Part 1: Issues / Problems Presented')
                    ->schema([
                        TextEntry::make('part1_subjective_objective')
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
                Section::make('Part 2: Assessment of Issues / Problems')
                    ->schema([
                        TextEntry::make('part2_assessment')
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),
                Section::make('Part 3: Treatment Planning and Action')
                    ->schema([
                        TextEntry::make('part3_treatment_plan')
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
                Section::make('Part 4: Evaluation of Counseling Session')
                    ->schema([
                        TextEntry::make('part4_evaluation')
                            ->hiddenLabel()
                            ->html()
                            ->prose()
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ])->columnSpanFull(),

                Section::make('')
                    ->schema([
                        TextEntry::make('attachments')
                            ->state(fn (IndividualCounsellingLog $record): string => collect($record->attachments)
                                ->map(fn (string $path): string => '<a href="'.e(Storage::disk(config('filament.default_filesystem_disk'))->temporaryUrl($path, now()->addMinutes(10))).'" target="_blank" class="text-primary-600 underline">'.e(basename($path)).'</a>')
                                ->implode('<br>'))
                            ->html()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('written_at')
                            ->label('Report written on')
                            ->dateTime()
                            ->placeholder('-'),
                    ])->columnSpanFull(),
            ]);
    }
}
