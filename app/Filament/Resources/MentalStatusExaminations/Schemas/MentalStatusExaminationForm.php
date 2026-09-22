<?php

namespace App\Filament\Resources\MentalStatusExaminations\Schemas;

use App\Models\Client;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MentalStatusExaminationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Client Information')
                    ->schema([
                        Select::make('client_id')
                            ->label('Client')
                            ->options(Client::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->unique(ignoreRecord: true),
                        DatePicker::make('date_of_interview'),
                    ])
                    ->columns(2),

                Section::make('General Presentation')
                    ->schema([
                        CheckboxList::make('dress_grooming')
                            ->label('Dress/grooming')
                            ->helperText('Tick all that apply.')
                            ->options([
                                'Neatly dressed/groomed' => 'Neatly dressed/groomed',
                                'Soiled clothing and/or malodorous' => 'Soiled clothing and/or malodorous',
                                'Carelessly dressed/groomed' => 'Carelessly dressed/groomed',
                            ])
                            ->columns(2),
                        Textarea::make('dress_grooming_other')
                            ->label('Other description(s) in this domain'),

                        CheckboxList::make('motor_function')
                            ->label('Gross/fine motor function')
                            ->helperText('Tick all that apply.')
                            ->options([
                                'Apparently normal gait and upper body movements' => 'Apparently normal gait and upper body movements',
                                'Rigidity' => 'Rigidity',
                                'Slow or halting gait/movements' => 'Slow or halting gait/movements',
                                'Shuffling gait' => 'Shuffling gait',
                                'Poor balance' => 'Poor balance',
                                'Tremors' => 'Tremors',
                            ])
                            ->columns(2),
                        CheckboxList::make('manual_dexterity')
                            ->label('Manual dexterity')
                            ->options([
                                'Good' => 'Good',
                                'Fair' => 'Fair',
                                'Poor' => 'Poor',
                            ])
                            ->columns(3),
                        Textarea::make('motor_function_other')
                            ->label('Other description(s) in this domain'),

                        CheckboxList::make('eye_contact')
                            ->label('Eye contact')
                            ->helperText('Tick all that apply.')
                            ->options([
                                'Good' => 'Good',
                                'Variable' => 'Variable',
                                'Avoidant' => 'Avoidant',
                                'Limited' => 'Limited',
                            ])
                            ->columns(2),
                        Textarea::make('eye_contact_other')
                            ->label('Other description(s) in this domain'),
                    ]),

                Section::make('Sensory & Comprehension')
                    ->schema([
                        CheckboxList::make('vision')
                            ->options([
                                'Intact for appointment purposes' => 'Intact for appointment purposes',
                                'Limited' => 'Limited',
                            ])
                            ->columns(2),
                        TextInput::make('vision_detail')
                            ->label('Visual acuity / detail if limited'),

                        CheckboxList::make('hearing')
                            ->options([
                                'Intact for appointment purposes' => 'Intact for appointment purposes',
                                'Limited' => 'Limited',
                            ])
                            ->columns(2),
                        TextInput::make('hearing_detail')
                            ->label('Hearing difficulty detail if limited'),

                        CheckboxList::make('verbal_comprehension')
                            ->label('Verbal comprehension')
                            ->options([
                                'Good' => 'Good',
                                'Fair' => 'Fair',
                                'Limited comprehension of questions or instructions' => 'Limited comprehension of questions or instructions',
                            ])
                            ->columns(3),
                        TextInput::make('verbal_comprehension_detail')
                            ->label('Detail if limited comprehension'),
                    ]),

                Section::make('Orientation')
                    ->description('To time, place and situation.')
                    ->schema([
                        TextInput::make('orientation_day_of_week')->label('Day of week'),
                        TextInput::make('orientation_month')->label('Month'),
                        TextInput::make('orientation_date')->label('Date'),
                        TextInput::make('orientation_year')->label('Year'),
                        TextInput::make('orientation_season')->label('Season'),
                        TextInput::make('orientation_building_type')->label('What type of building are we in?'),
                        TextInput::make('orientation_floor')->label('What floor?'),
                        TextInput::make('orientation_town')->label('Town'),
                        TextInput::make('orientation_county')->label('County'),
                        TextInput::make('orientation_state')->label('State'),
                        Radio::make('orientation_situation_rating')
                            ->label('Orientation to situation')
                            ->options([
                                'Good' => 'Good',
                                'Fair' => 'Fair',
                                'Limited' => 'Limited',
                            ])
                            ->inline(),
                        Textarea::make('orientation_situation_notes')
                            ->label('How would you describe what we are doing here today?')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Speech')
                    ->schema([
                        CheckboxList::make('speech_fluency')
                            ->label('Fluency or flow of speech')
                            ->options([
                                'Normal' => 'Normal',
                                'Halting' => 'Halting',
                                'Word-finding difficulty' => 'Word-finding difficulty',
                                'Garrulous' => 'Garrulous',
                                'Overly-talkative' => 'Overly-talkative',
                            ])
                            ->columns(2),
                        CheckboxList::make('speech_rate')
                            ->label('Rate')
                            ->options([
                                'Normal' => 'Normal',
                                'Slow' => 'Slow',
                                'Rapid' => 'Rapid',
                                'Pressured' => 'Pressured',
                            ])
                            ->columns(2),
                        CheckboxList::make('speech_volume')
                            ->label('Volume')
                            ->options([
                                'Normal' => 'Normal',
                                'Soft' => 'Soft',
                                'Loud' => 'Loud',
                                'Variable' => 'Variable',
                            ])
                            ->columns(2),
                        CheckboxList::make('speech_prosody')
                            ->label('Prosody or intonation')
                            ->options([
                                'Normal' => 'Normal',
                                'Flat or monotone' => 'Flat or monotone',
                                'Sing-song' => 'Sing-song',
                                'Other' => 'Other',
                            ])
                            ->columns(2)
                            ->live(),
                        TextInput::make('speech_prosody_other')
                            ->label('Please specify')
                            ->visible(fn (Get $get): bool => in_array('Other', $get('speech_prosody') ?? [], true)),
                    ]),

                Section::make('Affect, Mood, Sleep & Appetite')
                    ->schema([
                        CheckboxList::make('affect')
                            ->label('Affect (range of emotional expression)')
                            ->options([
                                'Normal range' => 'Normal range',
                                'Flat' => 'Flat',
                                'Labile' => 'Labile',
                                'Variable' => 'Variable',
                            ])
                            ->columns(2),

                        CheckboxList::make('mood')
                            ->options([
                                'Normal' => 'Normal',
                                'Sad' => 'Sad',
                                'Irritable' => 'Irritable',
                                'Expansive' => 'Expansive',
                                'Manic' => 'Manic',
                                'Other' => 'Other',
                            ])
                            ->columns(2)
                            ->live(),
                        TextInput::make('mood_other')
                            ->label('Please specify')
                            ->visible(fn (Get $get): bool => in_array('Other', $get('mood') ?? [], true)),

                        CheckboxList::make('sleep')
                            ->label('Sleep (by client self-report)')
                            ->options([
                                'Good' => 'Good',
                                'Restless' => 'Restless',
                                'Early' => 'Early',
                                'Middle and/or late insomnia' => 'Middle and/or late insomnia',
                            ])
                            ->columns(2),

                        CheckboxList::make('appetite')
                            ->options([
                                'Good' => 'Good',
                                'Limited' => 'Limited',
                                'Variable' => 'Variable',
                            ])
                            ->columns(3),
                    ]),

                Section::make('Thought & Motivation')
                    ->schema([
                        CheckboxList::make('thought_process')
                            ->options([
                                'Linear' => 'Linear',
                                'Goal-directed' => 'Goal-directed',
                                'Circumstantial' => 'Circumstantial',
                                'Tangential' => 'Tangential',
                                'Other' => 'Other',
                            ])
                            ->columns(2)
                            ->live(),
                        TextInput::make('thought_process_other')
                            ->label('Please specify')
                            ->visible(fn (Get $get): bool => in_array('Other', $get('thought_process') ?? [], true)),

                        CheckboxList::make('thought_content')
                            ->options([
                                'Normal content' => 'Normal content',
                                'Delusions' => 'Delusions',
                                'Hallucinations' => 'Hallucinations',
                                'Other' => 'Other',
                            ])
                            ->columns(2)
                            ->live(),
                        TextInput::make('thought_content_delusions_detail')
                            ->label('Delusions (describe)')
                            ->visible(fn (Get $get): bool => in_array('Delusions', $get('thought_content') ?? [], true)),
                        TextInput::make('thought_content_hallucinations_detail')
                            ->label('Hallucinations (describe)')
                            ->visible(fn (Get $get): bool => in_array('Hallucinations', $get('thought_content') ?? [], true)),
                        TextInput::make('thought_content_other_detail')
                            ->label('Other (describe)')
                            ->visible(fn (Get $get): bool => in_array('Other', $get('thought_content') ?? [], true)),

                        CheckboxList::make('motivation_effort')
                            ->label('Motivation/effort on testing')
                            ->options([
                                'Motivated/good effort' => 'Motivated/good effort',
                                'Variable motivation/effort' => 'Variable motivation/effort',
                                'Limited motivation/effort' => 'Limited motivation/effort',
                            ])
                            ->columns(3),
                    ]),

                Section::make('Clinical Notes')
                    ->schema([
                        Textarea::make('strengths')
                            ->helperText('Cognitive strengths (attention, memory, verbal skills, nonverbal/visual-spatial skills, executive skills) and social-emotional strengths (emotional control, coping skills, resilience, openness, relationships, asking for help).')
                            ->columnSpanFull(),
                        Textarea::make('other_observations')
                            ->label('Any other notable behavioral observations')
                            ->helperText('As they apply to interpretation of test results and/or case conceptualization.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
