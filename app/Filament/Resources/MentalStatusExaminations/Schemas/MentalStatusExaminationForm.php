<?php

namespace App\Filament\Resources\MentalStatusExaminations\Schemas;

use App\Models\Client;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MentalStatusExaminationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Grid::make(1)->schema([
                        Section::make('General Presentation')
                            ->schema([
                                Grid::make(1)->schema([
                                    Grid::make(2)
                                        ->dense()
                                        ->extraAttributes(['class' => 'mse-items'])
                                        ->schema([
                                            TextEntry::make('Dress/grooming')
                                                ->columnSpanFull()
                                                ->gap(false),
                                            CheckboxList::make('dress_grooming')
                                                ->hiddenLabel()
                                                ->label('Dress/grooming')
                                                ->helperText('Tick all that apply.')
                                                ->options([
                                                    'Neatly dressed/groomed' => 'Neatly dressed/groomed',
                                                    'Soiled clothing and/or malodorous' => 'Soiled clothing and/or malodorous',
                                                    'Carelessly dressed/groomed' => 'Carelessly dressed/groomed',
                                                ])
                                                ->columns(1),
                                            Textarea::make('dress_grooming_other')
                                                ->label('Other description(s) in this domain'),
                                        ]),
                                    Grid::make(2)
                                        ->dense()
                                        ->extraAttributes(['class' => 'mse-items'])
                                        ->schema([
                                            TextEntry::make('Gross/fine motor function')
                                                ->columnSpanFull()
                                                ->gap(false),
                                            CheckboxList::make('motor_function')
                                                ->hiddenLabel()
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
                                                ->columns(1),
                                            Grid::make(1)
            ->schema([
                CheckboxList::make('manual_dexterity')

                    ->label('Manual dexterity')
                    ->options([
                        'Good' => 'Good',
                        'Fair' => 'Fair',
                        'Poor' => 'Poor',
                    ]),
                Textarea::make('motor_function_other')
                    ->label('Other description(s) in this domain'),
            ]),

                                        ]),

                                    Grid::make(2)
                                        ->dense()
                                        ->extraAttributes(['class' => 'mse-items'])
                                        ->schema([
                                            TextEntry::make('Eye contact')
                                                ->columnSpanFull()
                                                ->gap(false),
                                            CheckboxList::make('eye_contact')
                                                ->hiddenLabel()
                                                ->label('Eye contact')
                                                ->helperText('Tick all that apply.')
                                                ->options([
                                                    'Good' => 'Good',
                                                    'Variable' => 'Variable',
                                                    'Avoidant' => 'Avoidant',
                                                    'Limited' => 'Limited',
                                                ])
                                                ->columns(1),
                                            Textarea::make('eye_contact_other')
                                                ->label('Other description(s) in this domain'),
                                        ]),
                                ]),

                            ]),

                        Section::make('Sensory & Comprehension')
                            ->schema([
                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Vision')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('vision')
                                            ->hiddenLabel()
                                            ->label('Vision')
                                            ->options([
                                                'Intact for appointment purposes' => 'Intact for appointment purposes',
                                                'Limited' => 'Limited',
                                            ])
                                            ->columns(1),
                                        TextInput::make('vision_detail')
                                            ->label('Visual acuity / detail if limited'),
                                    ]),

                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Hearing')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('hearing')
                                            ->hiddenLabel()
                                            ->label('Hearing')
                                            ->options([
                                                'Intact for appointment purposes' => 'Intact for appointment purposes',
                                                'Limited' => 'Limited',
                                            ])
                                            ->columns(1),
                                        TextInput::make('hearing_detail')
                                            ->label('Hearing difficulty detail if limited'),
                                    ]),

                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Verbal comprehension')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('verbal_comprehension')
                                            ->hiddenLabel()
                                            ->label('Verbal comprehension')
                                            ->options([
                                                'Good' => 'Good',
                                                'Fair' => 'Fair',
                                                'Limited comprehension of questions or instructions' => 'Limited comprehension of questions or instructions',
                                            ])
                                            ->columns(1),
                                        TextInput::make('verbal_comprehension_detail')
                                            ->label('Detail if limited comprehension'),
                                    ]),
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
                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Fluency or flow of speech')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('speech_fluency')
                                            ->hiddenLabel()
                                            ->label('Fluency or flow of speech')
                                            ->options([
                                                'Normal' => 'Normal',
                                                'Halting' => 'Halting',
                                                'Word-finding difficulty' => 'Word-finding difficulty',
                                                'Garrulous' => 'Garrulous',
                                                'Overly-talkative' => 'Overly-talkative',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull(),
                                    ]),
                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Rate')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('speech_rate')
                                            ->hiddenLabel()
                                            ->label('Rate')
                                            ->options([
                                                'Normal' => 'Normal',
                                                'Slow' => 'Slow',
                                                'Rapid' => 'Rapid',
                                                'Pressured' => 'Pressured',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull(),
                                    ]),
                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Volume')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('speech_volume')
                                            ->hiddenLabel()
                                            ->label('Volume')
                                            ->options([
                                                'Normal' => 'Normal',
                                                'Soft' => 'Soft',
                                                'Loud' => 'Loud',
                                                'Variable' => 'Variable',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull(),
                                    ]),
                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Prosody or intonation')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('speech_prosody')
                                            ->hiddenLabel()
                                            ->label('Prosody or intonation')
                                            ->options([
                                                'Normal' => 'Normal',
                                                'Flat or monotone' => 'Flat or monotone',
                                                'Sing-song' => 'Sing-song',
                                                'Other' => 'Other',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull()
                                            ->live(),
                                    ]),
                                TextInput::make('speech_prosody_other')
                                    ->label('Please specify')
                                    ->visible(fn (Get $get): bool => in_array('Other', $get('speech_prosody') ?? [], true)),
                            ]),

                        Section::make('Affect, Mood, Sleep & Appetite')
                            ->schema([
                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Affect (range of emotional expression)')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('affect')
                                            ->hiddenLabel()
                                            ->label('Affect (range of emotional expression)')
                                            ->options([
                                                'Normal range' => 'Normal range',
                                                'Flat' => 'Flat',
                                                'Labile' => 'Labile',
                                                'Variable' => 'Variable',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull(),
                                    ]),

                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Mood')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('mood')
                                            ->hiddenLabel()
                                            ->label('Mood')
                                            ->options([
                                                'Normal' => 'Normal',
                                                'Sad' => 'Sad',
                                                'Irritable' => 'Irritable',
                                                'Expansive' => 'Expansive',
                                                'Manic' => 'Manic',
                                                'Other' => 'Other',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull()
                                            ->live(),
                                    ]),
                                TextInput::make('mood_other')
                                    ->label('Please specify')
                                    ->visible(fn (Get $get): bool => in_array('Other', $get('mood') ?? [], true)),

                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Sleep (by client self-report)')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('sleep')
                                            ->hiddenLabel()
                                            ->label('Sleep (by client self-report)')
                                            ->options([
                                                'Good' => 'Good',
                                                'Restless' => 'Restless',
                                                'Early' => 'Early',
                                                'Middle and/or late insomnia' => 'Middle and/or late insomnia',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull(),
                                    ]),

                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Appetite')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('appetite')
                                            ->hiddenLabel()
                                            ->label('Appetite')
                                            ->options([
                                                'Good' => 'Good',
                                                'Limited' => 'Limited',
                                                'Variable' => 'Variable',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        Section::make('Thought & Motivation')
                            ->schema([
                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Thought process')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('thought_process')
                                            ->hiddenLabel()
                                            ->label('Thought process')
                                            ->options([
                                                'Linear' => 'Linear',
                                                'Goal-directed' => 'Goal-directed',
                                                'Circumstantial' => 'Circumstantial',
                                                'Tangential' => 'Tangential',
                                                'Other' => 'Other',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull()
                                            ->live(),
                                    ]),
                                TextInput::make('thought_process_other')
                                    ->label('Please specify')
                                    ->visible(fn (Get $get): bool => in_array('Other', $get('thought_process') ?? [], true)),

                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Thought content')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('thought_content')
                                            ->hiddenLabel()
                                            ->label('Thought content')
                                            ->options([
                                                'Normal content' => 'Normal content',
                                                'Delusions' => 'Delusions',
                                                'Hallucinations' => 'Hallucinations',
                                                'Other' => 'Other',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull()
                                            ->live(),
                                    ]),
                                TextInput::make('thought_content_delusions_detail')
                                    ->label('Delusions (describe)')
                                    ->visible(fn (Get $get): bool => in_array('Delusions', $get('thought_content') ?? [], true)),
                                TextInput::make('thought_content_hallucinations_detail')
                                    ->label('Hallucinations (describe)')
                                    ->visible(fn (Get $get): bool => in_array('Hallucinations', $get('thought_content') ?? [], true)),
                                TextInput::make('thought_content_other_detail')
                                    ->label('Other (describe)')
                                    ->visible(fn (Get $get): bool => in_array('Other', $get('thought_content') ?? [], true)),

                                Grid::make(2)
                                    ->dense()
                                    ->extraAttributes(['class' => 'mse-items'])
                                    ->schema([
                                        TextEntry::make('Motivation/effort on testing')
                                            ->columnSpanFull()
                                            ->gap(false),
                                        CheckboxList::make('motivation_effort')
                                            ->hiddenLabel()
                                            ->label('Motivation/effort on testing')
                                            ->options([
                                                'Motivated/good effort' => 'Motivated/good effort',
                                                'Variable motivation/effort' => 'Variable motivation/effort',
                                                'Limited motivation/effort' => 'Limited motivation/effort',
                                            ])
                                            ->columns(1)
                                            ->columnSpanFull(),
                                    ]),
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

                    ]),
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
                        ->grow(false),
                ])->columnSpanFull(),
            ]);

    }
}
