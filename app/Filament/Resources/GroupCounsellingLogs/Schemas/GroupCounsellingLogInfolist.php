<?php

namespace App\Filament\Resources\GroupCounsellingLogs\Schemas;

use App\Models\GroupCounsellingLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class GroupCounsellingLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('group.name')
                    ->label('Group Title / Code'),
                TextEntry::make('session_number')
                    ->label('Session no.')
                    ->placeholder('-'),
                TextEntry::make('total_sessions_planned')
                    ->label('Total sessions planned')
                    ->placeholder('-'),
                TextEntry::make('session_theme')
                    ->placeholder('-'),
                TextEntry::make('counselling_approach')
                    ->label('Counseling approach / theory')
                    ->placeholder('-'),
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
                    ->label('Venue / Organization')
                    ->placeholder('-'),
                TextEntry::make('facilitator_name')
                    ->label('Facilitator / Trainee')
                    ->placeholder('-'),
                TextEntry::make('supervisor_name')
                    ->label('Supervised by')
                    ->placeholder('-'),

                TextEntry::make('members')
                    ->label('Group members')
                    ->state(fn (GroupCounsellingLog $record): string => $record->memberRows()
                        ->map(fn (array $row): string => e("{$row['no']}. {$row['name']} — {$row['attendance']}".($row['participation'] !== '' ? " — participation: {$row['participation']}" : '')))
                        ->implode('<br>'))
                    ->html()
                    ->placeholder('-')
                    ->columnSpanFull(),

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
                    ->label('Part 4: Evaluation of Counseling Sessions')
                    ->html()
                    ->placeholder('-')
                    ->columnSpanFull(),

                TextEntry::make('attachments')
                    ->state(fn (GroupCounsellingLog $record): string => collect($record->attachments)
                        ->map(fn (string $path): string => '<a href="'.e(Storage::disk(config('filament.default_filesystem_disk'))->temporaryUrl($path, now()->addMinutes(10))).'" target="_blank" class="text-primary-600 underline">'.e(basename($path)).'</a>')
                        ->implode('<br>'))
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
