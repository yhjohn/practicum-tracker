<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Schemas;

use App\Models\Client;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IndividualCounsellingLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Grid::make(1)->schema([
                        Section::make('Part 1: Issues / Problems Presented')
                            ->description('Both subjective (the client\'s own story, feelings, thoughts, behaviors) and objective (your direct observations — eye contact, posture, tone, body language) information about the presenting concern.')
                            ->schema([
                                RichEditor::make('part1_subjective_objective')
                                    ->hiddenLabel()
                                    ->helperText('Onset & duration, triggering events, relevant client quotes, severity/frequency, and current impact on the client.')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Part 2: Assessment of Issues / Problems')
                            ->description('Your professional assessment and clinical conceptualization of the client\'s issues.')
                            ->schema([
                                RichEditor::make('part2_assessment')
                                    ->hiddenLabel()
                                    ->helperText('Core issue(s), theoretical conceptualization, cognitive distortions/maladaptive patterns, contributing factors, assessment tool scores, MSE observations, provisional diagnosis, client strengths.')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Part 3: Treatment Planning and Action')
                            ->description('The agreed treatment plan and the interventions/techniques used this session.')
                            ->schema([
                                RichEditor::make('part3_treatment_plan')
                                    ->hiddenLabel()
                                    ->helperText('Short-term & long-term goals, theoretical approach, techniques/interventions used, psychoeducation delivered, homework given, evaluation of treatment efficacy.')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Part 4: Evaluation of Counseling Session')
                            ->description('Progress and effectiveness of this session, and the plan for the next one.')
                            ->schema([
                                RichEditor::make('part4_evaluation')
                                    ->hiddenLabel()
                                    ->helperText('Client\'s self-reported progress, observed behavioral/emotional/cognitive changes, therapeutic relationship, goal achievement, risk indicators, plan for the next session.')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                    Section::make([
                        Select::make('client_id')
                            ->label('Client')
                            ->options(Client::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        DatePicker::make('session_date'),
                        TimePicker::make('start_time')
                            ->seconds(false)
                            ->label('Time'),
                        TextInput::make('duration_hours')
                            ->label('Duration (hours)')
                            ->numeric()
                            ->step(0.25),
                        TextInput::make('place')
                            ->label('Place / Organization'),
                        Radio::make('session_number')
                            ->label('Session no.')
                            ->options([
                                '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5',
                                '6' => '6', '7' => '7', '8' => '8', '9' => '9',
                            ])
                            ->inline(),
                        FileUpload::make('attachments')
                            ->multiple()
                            ->directory('individual-counselling-logs')
                            ->visibility('private')
                            ->downloadable()
                            ->openable(),
                    ]),
                ])
                    ->columnSpanFull(),
            ]);
    }
}
