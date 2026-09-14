<?php

namespace App\Filament\Schemas\Intakes;

use App\Livewire\PublicIntakeForm;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class IntakeWizard
{
    /**
     * @return array<Step>
     */
    public static function steps(PublicIntakeForm $livewire): array
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
    protected static function baseSteps(): array
    {
        return [
            Step::make('Personal Information')
                ->schema([
                    TextInput::make('name')->label('Full name'),
                    DatePicker::make('dob')->label('Date of birth'),
                    TextInput::make('age')->numeric()->minValue(0)->maxValue(120),
                    TextInput::make('phone')->tel(),
                    TextInput::make('email')->email(),
                    TextInput::make('occupation'),
                    Radio::make('sex')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female',
                            'Non-binary' => 'Non-binary',
                            'Prefer not to say' => 'Prefer not to say',
                        ])
                        ->inline()
                        ->helperText('Optional.'),
                    TextInput::make('emergency_name')->label("Emergency contact's full name"),
                    TextInput::make('emergency_relationship')->label('Relationship to you'),
                    TextInput::make('emergency_phone')->label('Emergency contact phone number')->tel(),
                ]),

            Step::make('Marital / Relationship Status')
                ->schema([
                    Radio::make('marital')
                        ->options([
                            'Single' => 'Single',
                            'Married' => 'Married',
                            'Divorced' => 'Divorced',
                            'Separated' => 'Separated',
                            'Widowed' => 'Widowed',
                            'In a relationship' => 'In a relationship',
                        ]),
                    TextInput::make('relationship_length')->label('Length of current relationship (if applicable)'),
                    TextInput::make('marriages')->label('Number of previous marriages (if applicable)')->numeric()->minValue(0),
                ]),

            Step::make('Education')
                ->schema([
                    TextInput::make('education')->label('Highest level of education completed'),
                    Radio::make('education_current')
                        ->label('Currently enrolled in')
                        ->options([
                            'College/university' => 'College/university',
                            'Vocational training' => 'Vocational training',
                            'Graduate school' => 'Graduate school',
                            'Other training' => 'Other training',
                        ]),
                    Textarea::make('education_details')->label('Relevant details or circumstances'),
                ]),

            Step::make('Career / Employment')
                ->schema([
                    TextInput::make('job_title')->label('Current employment / job title'),
                    Textarea::make('employment_history')->label('Employment history and relevant circumstances'),
                    Textarea::make('job_satisfaction')->label('Job satisfaction (1–5) and why'),
                ]),

            Step::make('Personal Strengths and Supports')
                ->schema([
                    Textarea::make('strength_activities')->label('What activities do you enjoy or feel successful doing?'),
                    Textarea::make('strengths')->label('What personal qualities or strengths do you see in yourself?'),
                    Textarea::make('others_strengths')->label('What strengths might others say you have?'),
                    Textarea::make('supports')->label('Who or what supports you (people, activities, beliefs, etc.)?'),
                ]),

            Step::make('Traumatic Experiences or Significant Losses')
                ->schema([
                    CheckboxList::make('trauma')
                        ->helperText('Tick any that apply. Share only what you feel comfortable sharing.')
                        ->options([
                            'Bullying' => 'Bullying',
                            'Chronic illness' => 'Chronic illness',
                            'Death/loss' => 'Death/loss',
                            'Divorce/separation' => 'Divorce/separation',
                            'Emotional abuse' => 'Emotional abuse',
                            'Financial hardship' => 'Financial hardship',
                            'Physical/medical trauma' => 'Physical/medical trauma',
                            'Sexual abuse' => 'Sexual abuse',
                            'Stalking' => 'Stalking',
                            'Other' => 'Other',
                        ]),
                    Textarea::make('trauma_details')->label('Anything you would like to add'),
                ]),

            Step::make('Family History')
                ->schema([
                    Textarea::make('family_description')->label('How would you describe your family?'),
                    Textarea::make('childhood_experience')->label('Are there childhood or family experiences important for your counsellor to know?'),
                    Textarea::make('family_support')->label('Did you receive the support you needed from family or friends?'),
                ]),

            Step::make('Counselling / Medical History')
                ->schema([
                    Textarea::make('counselling_history')->label('Have you previously seen a counsellor or therapist? Where, when, and why?'),
                    Textarea::make('diagnosis')->label('Any previous mental health diagnosis you wish to share?'),
                    Textarea::make('therapy_experience')->label('What was helpful or unhelpful in previous support?'),
                    Textarea::make('medical_history')->label('Psychiatric services, hospitalisation, or medication relevant to your wellbeing (if comfortable)'),
                ]),

            Step::make('Substance Use')
                ->schema([
                    Textarea::make('substance_use')->label('Do you currently use alcohol, tobacco, or other substances? If yes, describe type, frequency, and amount if comfortable.'),
                    Textarea::make('substance_treatment')->label('Have you received support or treatment related to substance use?'),
                ]),

            Step::make('Legal Issues')
                ->schema([
                    Textarea::make('legal_issues')->label('Are there current or past legal issues affecting you or your family?'),
                    Textarea::make('legal_impact')->label('How have these issues affected you or your loved ones?'),
                ]),

            Step::make('Emotional / Mental Wellbeing')
                ->schema([
                    Textarea::make('emotions')->label('How would you describe your emotions and mood recently?'),
                    Textarea::make('safety')->label('Have you had thoughts about harming yourself or someone else? Share only what you feel comfortable sharing; your counsellor may discuss safety with you.'),
                    Textarea::make('support_system')->label('Who could you turn to for support?'),
                    Textarea::make('current_stress')->label('What significant problems or stresses are you facing now?'),
                    Textarea::make('memory')->label('Any memory or concentration concerns?'),
                ]),

            Step::make('Spirituality / Beliefs')
                ->schema([
                    Textarea::make('belief_system')->label('Is there a belief system or spiritual practice your counsellor should be aware of?'),
                    Textarea::make('spiritual_upbringing')->label('Did religion or spiritual practice play a part in your upbringing?'),
                ]),

            Step::make('Reasons for Seeking Counselling')
                ->schema([
                    Textarea::make('seeking_reason')->label('Briefly describe the concern for which you are seeking counselling.'),
                    Textarea::make('first_occurred')->label('When did you first notice this concern?'),
                    Textarea::make('main_concern')->label('What is most concerning for you right now?'),
                    Textarea::make('family_concern')->label('What concerns have family or friends expressed, if any?'),
                    Textarea::make('counselling_outcome')->label('What would you like to achieve through counselling?'),
                    Textarea::make('anything_else')->label('Anything else you would like to share?'),
                ]),
        ];
    }

    protected static function summaryStep(): Step
    {
        return Step::make('Review & Sign')
            ->schema([
                ...IntakeSummary::sections(),

                Section::make('Confidentiality Acknowledgement and Signature')
                    ->description('My counsellor has explained the purpose of this intake form and the confidentiality of information shared, including its limits. I understand that I may ask questions about these matters.')
                    ->schema([
                        SignaturePad::make('signature')
                            ->label('Client signature')
                            ->required(),
                        DatePicker::make('signature_date')->label('Date')->default(now()),
                    ]),
            ]);
    }
}
