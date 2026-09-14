<?php

namespace App\Filament\Schemas\Intakes;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class IntakeSummary
{
    /**
     * Read-only sections covering every intake field, shared between the
     * public wizard's review step and the staff-facing client infolist.
     *
     * @return array<Section>
     */
    public static function sections(): array
    {
        return [
            Section::make('Personal Information')
                ->schema([
                    TextEntry::make('name')->label('Full name'),
                    TextEntry::make('dob')->label('Date of birth')->date(),
                    TextEntry::make('age'),
                    TextEntry::make('phone'),
                    TextEntry::make('email'),
                    TextEntry::make('occupation'),
                    TextEntry::make('sex'),
                    TextEntry::make('emergency_name')->label("Emergency contact's full name"),
                    TextEntry::make('emergency_relationship')->label('Relationship to you'),
                    TextEntry::make('emergency_phone')->label('Emergency contact phone number'),
                ])
                ->columns(2),

            Section::make('Marital / Relationship Status')
                ->schema([
                    TextEntry::make('marital')->label('Marital status'),
                    TextEntry::make('relationship_length')->label('Length of current relationship'),
                    TextEntry::make('marriages')->label('Number of previous marriages'),
                ])
                ->columns(2),

            Section::make('Education')
                ->schema([
                    TextEntry::make('education')->label('Highest level of education completed'),
                    TextEntry::make('education_current')->label('Currently enrolled in'),
                    TextEntry::make('education_details')->label('Relevant details or circumstances'),
                ])
                ->columns(2),

            Section::make('Career / Employment')
                ->schema([
                    TextEntry::make('job_title')->label('Current employment / job title'),
                    TextEntry::make('employment_history')->label('Employment history and relevant circumstances'),
                    TextEntry::make('job_satisfaction')->label('Job satisfaction (1–5) and why'),
                ])
                ->columns(2),

            Section::make('Personal Strengths and Supports')
                ->schema([
                    TextEntry::make('strength_activities')->label('Activities you enjoy or feel successful doing'),
                    TextEntry::make('strengths')->label('Personal qualities or strengths'),
                    TextEntry::make('others_strengths')->label('Strengths others might say you have'),
                    TextEntry::make('supports')->label('Who or what supports you'),
                ])
                ->columns(2),

            Section::make('Traumatic Experiences or Significant Losses')
                ->schema([
                    TextEntry::make('trauma')
                        ->label('Experiences ticked')
                        ->formatStateUsing(fn (mixed $state): string => filled($state) ? implode(', ', (array) $state) : '—'),
                    TextEntry::make('trauma_details')->label('Anything you would like to add'),
                ])
                ->columns(2),

            Section::make('Family History')
                ->schema([
                    TextEntry::make('family_description')->label('How would you describe your family?'),
                    TextEntry::make('childhood_experience')->label('Childhood or family experiences'),
                    TextEntry::make('family_support')->label('Support received from family or friends'),
                ])
                ->columns(2),

            Section::make('Counselling / Medical History')
                ->schema([
                    TextEntry::make('counselling_history')->label('Previous counsellor or therapist'),
                    TextEntry::make('diagnosis')->label('Previous mental health diagnosis'),
                    TextEntry::make('therapy_experience')->label('What was helpful or unhelpful'),
                    TextEntry::make('medical_history')->label('Psychiatric services, hospitalisation, or medication'),
                ])
                ->columns(2),

            Section::make('Substance Use')
                ->schema([
                    TextEntry::make('substance_use')->label('Alcohol, tobacco, or other substance use'),
                    TextEntry::make('substance_treatment')->label('Support or treatment received'),
                ])
                ->columns(2),

            Section::make('Legal Issues')
                ->schema([
                    TextEntry::make('legal_issues')->label('Current or past legal issues'),
                    TextEntry::make('legal_impact')->label('How these issues have affected you'),
                ])
                ->columns(2),

            Section::make('Emotional / Mental Wellbeing')
                ->schema([
                    TextEntry::make('emotions')->label('Emotions and mood recently'),
                    TextEntry::make('safety')->label('Thoughts about harming yourself or someone else'),
                    TextEntry::make('support_system')->label('Who you could turn to for support'),
                    TextEntry::make('current_stress')->label('Significant problems or stresses'),
                    TextEntry::make('memory')->label('Memory or concentration concerns'),
                ])
                ->columns(2),

            Section::make('Spirituality / Beliefs')
                ->schema([
                    TextEntry::make('belief_system')->label('Belief system or spiritual practice'),
                    TextEntry::make('spiritual_upbringing')->label('Religion or spiritual practice in upbringing'),
                ])
                ->columns(2),

            Section::make('Reasons for Seeking Counselling')
                ->schema([
                    TextEntry::make('seeking_reason')->label('Concern for which you are seeking counselling'),
                    TextEntry::make('first_occurred')->label('When you first noticed this concern'),
                    TextEntry::make('main_concern')->label('What is most concerning right now'),
                    TextEntry::make('family_concern')->label('Concerns expressed by family or friends'),
                    TextEntry::make('counselling_outcome')->label('What you would like to achieve'),
                    TextEntry::make('anything_else')->label('Anything else you would like to share'),
                ])
                ->columns(2),
        ];
    }
}
