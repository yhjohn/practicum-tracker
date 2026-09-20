<?php

namespace App\Filament\Resources\GroupCounsellingLogs\Schemas;

use App\Models\Client;
use App\Models\Group;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class GroupCounsellingLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Grid::make(1)->schema([
                        Section::make('Group Members: Attendance & Progress')
                            ->description('Members are loaded from the group. Untick anyone who did not attend.')
                            ->schema([
                                Repeater::make('members')
                                    ->hiddenLabel()
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->schema([
                                        Select::make('client_id')
                                            ->label('Name / Client Code')
                                            ->options(Client::query()->pluck('name', 'id'))
                                            ->disabled()
                                            ->dehydrated(),
                                        Toggle::make('attended')
                                            ->label('Attended')
                                            ->default(true),
                                        TextInput::make('attendance_notes')
                                            ->label('Attendance notes'),
                                        Select::make('participation_level')
                                            ->options(['Low' => 'Low', 'Moderate' => 'Moderate', 'High' => 'High']),
                                        Textarea::make('progress_notes')
                                            ->label('Facilitator\'s notes / observations')
                                            ->rows(2)
                                            ->columnSpanFull(),
                                    ])
                                    ->columns(2),
                            ]),
                        Section::make('Part 1: Issues / Problems Presented')
                            ->description('Subjective (members\' own accounts) and objective (your observations of group dynamics, non-verbal cues, participation) information.')
                            ->schema([
                                RichEditor::make('part1_subjective_objective')
                                    ->hiddenLabel()
                                    ->helperText('Member-by-member summary labelled Cognitive (C), Affective (A) or Behavioural (B), onset & duration, group observations, common themes, verbatim quotes.')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Part 2: Assessment of Issues / Problems')
                            ->description('Your professional assessment and clinical conceptualization of the group\'s issue.')
                            ->schema([
                                RichEditor::make('part2_assessment')
                                    ->hiddenLabel()
                                    ->helperText('Theoretical conceptualization, C/A/B patterns, contributing factors, group dynamic assessment (e.g. Tuckman), MSE, standardized tools, strengths, working conceptualization.')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Part 3: Treatment Planning and Action')
                            ->description('Treatment goals and the interventions implemented this session.')
                            ->schema([
                                RichEditor::make('part3_treatment_plan')
                                    ->hiddenLabel()
                                    ->helperText('Session goals, theoretical approach, techniques, psychoeducation, group activities, homework, referral plan, monitoring plan.')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Part 4: Evaluation of Counseling Sessions')
                            ->description('Effectiveness of this session and the plan for the next one.')
                            ->schema([
                                RichEditor::make('part4_evaluation')
                                    ->hiddenLabel()
                                    ->helperText('Members\' self-reported experience, behavioural/emotional changes, cognitive shifts, group dynamics, goal achievement, risk indicators, self-reflection, plan for next session.')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                    Section::make([
                        Select::make('group_id')
                            ->label('Group Title / Code')
                            ->options(Group::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                                'members',
                                Group::find($state)?->clients
                                    ->map(fn (Client $client): array => ['client_id' => $client->id, 'attended' => true])
                                    ->all() ?? [],
                            )),
                        TextInput::make('session_number')
                            ->label('Session no.')
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('total_sessions_planned')
                            ->label('Total sessions planned')
                            ->numeric()
                            ->minValue(1),
                        TextInput::make('session_theme'),
                        TextInput::make('counselling_approach')
                            ->label('Counseling approach / theory'),
                        DatePicker::make('session_date'),
                        TimePicker::make('start_time')
                            ->seconds(false)
                            ->label('Time'),
                        TextInput::make('duration_hours')
                            ->label('Duration (hours)')
                            ->numeric()
                            ->step(0.25),
                        TextInput::make('place')
                            ->label('Venue / Organization'),
                        TextInput::make('facilitator_name')
                            ->label('Facilitator / Trainee')
                            ->default(fn (): ?string => auth()->user()?->name),
                        TextInput::make('supervisor_name')
                            ->label('Supervised by'),
                        FileUpload::make('attachments')
                            ->multiple()
                            ->directory('group-counselling-logs')
                            ->visibility('private')
                            ->downloadable()
                            ->openable(),
                    ]),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
