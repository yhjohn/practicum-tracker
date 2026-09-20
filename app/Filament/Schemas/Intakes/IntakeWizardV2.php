<?php

namespace App\Filament\Schemas\Intakes;

use App\Livewire\PublicIntakeFormV2;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class IntakeWizardV2
{
    /**
     * @return array<Step>
     */
    public static function steps(PublicIntakeFormV2 $livewire): array
    {
        $steps = array_map(
            fn (Step $step): Step => $step->afterValidation(fn () => $livewire->saveProgress()),
            static::baseSteps(),
        );

        $steps[] = static::summaryStep();

        return $steps;
    }

    /**
     * @return array<Step>
     */
    public static function baseSteps(): array
    {
        return [
            Step::make('Personal Information')
                ->schema([
                    TextInput::make('name')->label('Full name'),
                    TextInput::make('age')->numeric()->minValue(0)->maxValue(120),
                    TextInput::make('phone')->tel()->prefix('+60 '),
                    TextInput::make('email')->email(),
                    Textarea::make('address'),
                    Radio::make('marital')
                        ->inline()
                        ->label('Relationship status')
                        ->options([
                            'Single' => 'Single',
                            'Married' => 'Married',
                            'In a relationship' => 'In a relationship',
                            'Separated / divorced' => 'Separated / divorced',
                            'Widowed' => 'Widowed',
                            'Other' => 'Other',
                            'Prefer not to say' => 'Prefer not to say',
                        ]),
                    TextInput::make('occupation'),
                    Radio::make('sex')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female',
                            'Non-binary' => 'Non-binary',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->inline(),
                    Fieldset::make('Emergency Contact')->schema([
                        Callout::make('')
                            ->description('Providing an emergency contact is optional. If you choose to provide one, your counselor will only contact this person in an urgent situation involving your safety or wellbeing, and strictly within the limits of confidentiality.')
                            ->warning()
                            ->columnSpanFull(),
                        Grid::make(3)->schema([
                            TextInput::make('emergency_name')->label("Emergency contact's full name"),
                            TextInput::make('emergency_relationship')->label('Relationship to you'),
                            TextInput::make('emergency_phone')->label('Emergency contact phone number')->tel()->prefix('+60 '),
                        ])->columnSpanFull(),
                    ]),
                ]),

            Step::make('Education, Career & Personal Strengths')
                ->schema([
                    Radio::make('education_level')
                        ->label('What is the highest level of education you have completed?')
                        ->options([
                            'Primary education' => 'Primary education',
                            'Secondary education (SPM or equivalent)' => 'Secondary education (SPM or equivalent)',
                            'Certificate or diploma' => 'Certificate or diploma',
                            'Vocational / technical training' => 'Vocational / technical training',
                            "Bachelor's degree" => "Bachelor's degree",
                            "Postgraduate degree (Master's or PhD)" => "Postgraduate degree (Master's or PhD)",
                            'Other' => 'Other',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->columns(2)
                        ->live(),
                    TextInput::make('education_level_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => $get('education_level') === 'Other'),
                    Radio::make('job_satisfaction')
                        ->label('How satisfied are you with your current job?')
                        ->options([
                            '1 – Very dissatisfied' => '1 – Very dissatisfied',
                            '2 – Dissatisfied' => '2 – Dissatisfied',
                            '3 – Neutral' => '3 – Neutral',
                            '4 – Satisfied' => '4 – Satisfied',
                            '5 – Very satisfied' => '5 – Very satisfied',
                            'Not currently employed' => 'Not currently employed',
                        ])
                        ->columns(2),

                    CheckboxList::make('enjoyable_activities')
                        ->label('What activities do you enjoy or feel successful doing?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Sports / physical activities' => 'Sports / physical activities',
                            'Arts / creative activities' => 'Arts / creative activities',
                            'Music, singing or dancing' => 'Music, singing or dancing',
                            'Reading / learning' => 'Reading / learning',
                            'Cooking / baking' => 'Cooking / baking',
                            'Time with family or friends' => 'Time with family or friends',
                            'Helping others' => 'Helping others',
                            'Work / academic activities' => 'Work / academic activities',
                            'Hobbies' => 'Hobbies',
                            'Other' => 'Other',
                        ])
                        ->columns(3)
                        ->live(),
                    TextInput::make('enjoyable_activities_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('enjoyable_activities') ?? [], true)),

                    CheckboxList::make('personal_strengths')
                        ->label('What personal qualities or strengths do you see in yourself?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Kindness' => 'Kindness',
                            'Patience' => 'Patience',
                            'Creativity' => 'Creativity',
                            'Confidence' => 'Confidence',
                            'Responsibility' => 'Responsibility',
                            'Determination' => 'Determination',
                            'Honesty' => 'Honesty',
                            'Communication' => 'Communication',
                            'Problem-solving' => 'Problem-solving',
                            'Adaptability' => 'Adaptability',
                            'Independence' => 'Independence',
                            'Not sure' => 'Not sure',
                            'Other' => 'Other',
                        ])
                        ->columns(3)
                        ->live(),
                    TextInput::make('personal_strengths_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('personal_strengths') ?? [], true)),

                    CheckboxList::make('coping_resources')
                        ->label('What activities or resources help you cope with difficulties?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Loved ones' => 'Loved ones',
                            'Exercise / sports' => 'Exercise / sports',
                            'Music' => 'Music',
                            'Hobbies' => 'Hobbies',
                            'Rest / alone time' => 'Rest / alone time',
                            'Prayer / meditation' => 'Prayer / meditation',
                            'Nature' => 'Nature',
                            'Talking about feelings' => 'Talking about feelings',
                            'Other' => 'Other',
                        ])
                        ->columns(3)
                        ->live(),
                    TextInput::make('coping_resources_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('coping_resources') ?? [], true)),

                    Textarea::make('strengths_to_build')->label('What strengths or support would you like to build on?'),
                ]),

            Step::make('Family & Social Background')
                ->schema([
                    CheckboxList::make('family_relationships')
                        ->label('How would you describe your family relationships?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Close and supportive' => 'Close and supportive',
                            'Generally positive' => 'Generally positive',
                            'Sometimes difficult / stressful' => 'Sometimes difficult / stressful',
                            'Frequent conflicts / misunderstandings' => 'Frequent conflicts / misunderstandings',
                            'Distant / not connected' => 'Distant / not connected',
                            'Complicated or mixed' => 'Complicated or mixed',
                            'Prefer not to say' => 'Prefer not to say',
                            'Other' => 'Other',
                        ])
                        ->columns(2)
                        ->live(),
                    TextInput::make('family_relationships_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('family_relationships') ?? [], true)),

                    CheckboxList::make('childhood_experiences')
                        ->label('Are there childhood or family experiences you would like your counsellor to know about?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Positive experiences' => 'Positive experiences',
                            'Family conflicts' => 'Family conflicts',
                            'Changes in family structure / living arrangements' => 'Changes in family structure / living arrangements',
                            'Loss of a loved one' => 'Loss of a loved one',
                            'Bullying or rejection' => 'Bullying or rejection',
                            'Neglect / lack of support' => 'Neglect / lack of support',
                            'Difficult or stressful experiences' => 'Difficult or stressful experiences',
                            'No, not at the moment' => 'No, not at the moment',
                            'Prefer not to say' => 'Prefer not to say',
                            'Other' => 'Other',
                        ])
                        ->columns(2)
                        ->live(),
                    TextInput::make('childhood_experiences_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('childhood_experiences') ?? [], true)),

                    Radio::make('family_support_received')
                        ->label('Did you receive the support you needed from family or friends when facing difficulties?')
                        ->options([
                            'Always' => 'Always',
                            'Most of the time' => 'Most of the time',
                            'Sometimes' => 'Sometimes',
                            'Rarely' => 'Rarely',
                            'Never' => 'Never',
                            'Not sure' => 'Not sure',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->columns(2),
                    CheckboxList::make('beliefs')
                        ->label('Do you have religious, spiritual or personal beliefs you would like your counsellor to be aware of?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Buddhism' => 'Buddhism',
                            'Christianity' => 'Christianity',
                            'Hinduism' => 'Hinduism',
                            'Islam' => 'Islam',
                            'Taoism' => 'Taoism',
                            'Personal beliefs not linked to a religion' => 'Personal beliefs not linked to a religion',
                            'No particular beliefs' => 'No particular beliefs',
                            'Prefer not to say' => 'Prefer not to say',
                            'Other' => 'Other',
                        ])
                        ->columns(2)
                        ->live(),
                    TextInput::make('beliefs_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('beliefs') ?? [], true)),

                    Radio::make('spiritual_upbringing_role')
                        ->label('Did religion or spirituality play a role in your upbringing?')
                        ->options([
                            'Significant role' => 'Significant role',
                            'Some role' => 'Some role',
                            'No role' => 'No role',
                            'Unsure' => 'Unsure',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->columns(2),
                    CheckboxList::make('substances_used')
                        ->label('Do you currently use any of the following substances?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Alcohol' => 'Alcohol',
                            'Tobacco / cigarettes' => 'Tobacco / cigarettes',
                            'Drugs' => 'Drugs',
                            'Other' => 'Other',
                            'None' => 'None',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->columns(2)
                        ->live(),
                    TextInput::make('substances_used_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('substances_used') ?? [], true)),

                    Radio::make('substance_use_frequency')
                        ->label('If yes, how frequently do you use them?')
                        ->options([
                            'Daily' => 'Daily',
                            'Several times a week' => 'Several times a week',
                            'Weekly' => 'Weekly',
                            'A few times a month' => 'A few times a month',
                            'Rarely / occasionally' => 'Rarely / occasionally',
                            'Other' => 'Other',
                        ])
                        ->columns(2)
                        ->live()
                        ->visible(fn (Get $get): bool => filled(array_diff($get('substances_used') ?? [], ['None', 'Prefer not to say']))),
                    TextInput::make('substance_use_frequency_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => $get('substance_use_frequency') === 'Other'),
                    Textarea::make('background_notes')->label('Is there anything else about your background, family, beliefs or support system you would like to share?'),
                ]),
            Step::make('Counselling / Medical History')
                ->schema([
                    Radio::make('previous_counselling')
                        ->label('Have you previously received counselling or therapy?')
                        ->inline()
                        ->options([
                            'Yes' => 'Yes',
                            'No' => 'No',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->live(),
                    Radio::make('previous_counselling_when')
                        ->label('If yes, when was the main reason for seeking support?')
                        ->inline()
                        ->options([
                            'Within past 6 months' => 'Within past 6 months',
                            '6 months–1 year ago' => '6 months–1 year ago',
                            'More than 1 year ago' => 'More than 1 year ago',
                        ])
                        ->visible(fn (Get $get): bool => $get('previous_counselling') === 'Yes'),

                    Radio::make('mental_health_diagnosis')
                        ->label('Have you previously received a mental health diagnosis you would like to share?')
                        ->options([
                            'Yes' => 'Yes',
                            'No' => 'No',
                            'Unsure' => 'Unsure',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->live(),
                    Textarea::make('mental_health_diagnosis_details')
                        ->label('If yes, you may specify the diagnosis')
                        ->visible(fn (Get $get): bool => $get('mental_health_diagnosis') === 'Yes'),

                    CheckboxList::make('psychiatric_services')
                        ->label('Have you received psychiatric services, hospitalisation or medication related to mental health?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Psychiatric consultation' => 'Psychiatric consultation',
                            'Hospitalisation' => 'Hospitalisation',
                            'Medication' => 'Medication',
                            'Currently receiving support' => 'Currently receiving support',
                            'None' => 'None',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->columns(2),

                    Textarea::make('previous_support_feedback')->label('What did you find helpful or unhelpful about previous support?'),

                    CheckboxList::make('mood_recent')
                        ->label('How would you describe your emotions and mood recently?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Happy / content' => 'Happy / content',
                            'Calm / relaxed' => 'Calm / relaxed',
                            'Worried / anxious' => 'Worried / anxious',
                            'Sad / low' => 'Sad / low',
                            'Stressed / overwhelmed' => 'Stressed / overwhelmed',
                            'Frustrated / irritable' => 'Frustrated / irritable',
                            'Lonely / disconnected' => 'Lonely / disconnected',
                            'Emotionally exhausted' => 'Emotionally exhausted',
                            'Mood changes frequently' => 'Mood changes frequently',
                            'Other' => 'Other',
                            'Not sure' => 'Not sure',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->columns(2)
                        ->live(),
                    TextInput::make('mood_recent_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('mood_recent') ?? [], true)),

                    Radio::make('harm_thoughts')
                        ->label('Have you recently had thoughts about harming yourself or someone else?')
                        ->options([
                            'No' => 'No',
                            'Yes, myself' => 'Yes, myself',
                            'Yes, someone else' => 'Yes, someone else',
                            'Yes, both' => 'Yes, both',
                            'Unsure' => 'Unsure',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->columns(2)
                        ->live(),
                    Textarea::make('harm_thoughts_response')
                        ->label('If yes, you may share what support you need here or discuss it privately with your counsellor')
                        ->visible(fn (Get $get): bool => filled($get('harm_thoughts')) && $get('harm_thoughts') !== 'No'),

                    CheckboxList::make('current_stressors')
                        ->label('What significant problems or stresses are you currently facing?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Academic / exam stress' => 'Academic / exam stress',
                            'Work / career' => 'Work / career',
                            'Family' => 'Family',
                            'Relationship / friendship' => 'Relationship / friendship',
                            'Financial' => 'Financial',
                            'Health / wellbeing' => 'Health / wellbeing',
                            'Life changes' => 'Life changes',
                            'Grief / loss' => 'Grief / loss',
                            'Self-esteem / confidence' => 'Self-esteem / confidence',
                            'Other' => 'Other',
                            'None at present' => 'None at present',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->columns(2)
                        ->live(),
                    TextInput::make('current_stressors_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('current_stressors') ?? [], true)),

                    Radio::make('current_stressors_impact')
                        ->label('How much are these concerns affecting your daily life?')
                        ->options([
                            'Not at all' => 'Not at all',
                            'Slightly' => 'Slightly',
                            'Moderately' => 'Moderately',
                            'Significantly' => 'Significantly',
                            'Severely' => 'Severely',
                            'Not sure' => 'Not sure',
                        ])
                        ->columns(2),

                    Textarea::make('main_concern')->label('What is the main concern you are seeking counselling for?'),

                    CheckboxList::make('counselling_goals')
                        ->label('What would you like to achieve through counselling?')
                        ->helperText('Select all that apply.')
                        ->options([
                            'Understand feelings / thoughts' => 'Understand feelings / thoughts',
                            'Manage stress / emotions' => 'Manage stress / emotions',
                            'Improve relationships' => 'Improve relationships',
                            'Build confidence / self-esteem' => 'Build confidence / self-esteem',
                            'Develop coping strategies' => 'Develop coping strategies',
                            'Explore solutions' => 'Explore solutions',
                            'Improve overall wellbeing' => 'Improve overall wellbeing',
                            'Not sure yet' => 'Not sure yet',
                            'Other' => 'Other',
                        ])
                        ->columns(2)
                        ->live(),
                    TextInput::make('counselling_goals_other')
                        ->label('Please specify')
                        ->visible(fn (Get $get): bool => in_array('Other', $get('counselling_goals') ?? [], true)),

                    Textarea::make('anything_else')->label('Is there anything else you would like to share with your counsellor?'),
                ]),
        ];
    }

    protected static function summaryStep(): Step
    {
        return Step::make('Review & Submit')
            ->schema([
                ...IntakeSummaryV2::sections(),

                Section::make('Confidentiality Acknowledgement and Signature')
                    ->description('My counsellor has explained the purpose of this intake form and the confidentiality of information shared, including its limits. I understand that I may ask questions about these matters.')
                    ->schema([
                        SignaturePad::make('signature')
                            ->backgroundColor('#ffffff')
                            ->backgroundColorOnDark('#ffffff')
                            ->penColor('#000')
                            ->penColorOnDark('#000')
                            ->label('Client signature')
                            ->required(),
                    ]),
            ]);
    }
}
