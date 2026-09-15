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
    public static function sections(string $prefix = ''): array
    {
        return [
            Section::make('Personal Information')
                ->schema([
                    TextEntry::make("{$prefix}name")->label('Full name'),
                    TextEntry::make("{$prefix}dob")->label('Date of birth')->date(),
                    TextEntry::make("{$prefix}age"),
                    TextEntry::make("{$prefix}phone"),
                    TextEntry::make("{$prefix}email"),
                    TextEntry::make("{$prefix}occupation"),
                    TextEntry::make("{$prefix}sex"),
                    TextEntry::make("{$prefix}emergency_name")->label("Emergency contact's full name"),
                    TextEntry::make("{$prefix}emergency_relationship")->label('Relationship to you'),
                    TextEntry::make("{$prefix}emergency_phone")->label('Emergency contact phone number'),
                ])
                ->columns(2),

            Section::make('Marital / Relationship Status')
                ->schema([
                    TextEntry::make("{$prefix}marital")->label('Marital status'),
                    TextEntry::make("{$prefix}relationship_length")->label('Length of current relationship'),
                    TextEntry::make("{$prefix}marriages")->label('Number of previous marriages'),
                ])
                ->columns(2),

            Section::make('Education')
                ->schema([
                    TextEntry::make("{$prefix}education")->label('Highest level of education completed'),
                    TextEntry::make("{$prefix}education_current")->label('Currently enrolled in'),
                    TextEntry::make("{$prefix}education_details")->label('Relevant details or circumstances'),
                ])
                ->columns(2),

            Section::make('Career / Employment')
                ->schema([
                    TextEntry::make("{$prefix}job_title")->label('Current employment / job title'),
                    TextEntry::make("{$prefix}employment_history")->label('Employment history and relevant circumstances'),
                    TextEntry::make("{$prefix}job_satisfaction")->label('Job satisfaction (1–5) and why'),
                ])
                ->columns(2),

            Section::make('Personal Strengths and Supports')
                ->schema([
                    TextEntry::make("{$prefix}strength_activities")->label('Activities you enjoy or feel successful doing'),
                    TextEntry::make("{$prefix}strengths")->label('Personal qualities or strengths'),
                    TextEntry::make("{$prefix}others_strengths")->label('Strengths others might say you have'),
                    TextEntry::make("{$prefix}supports")->label('Who or what supports you'),
                ])
                ->columns(2),

            Section::make('Traumatic Experiences or Significant Losses')
                ->schema([
                    TextEntry::make("{$prefix}trauma")
                        ->label('Experiences ticked')
                        ->formatStateUsing(fn (mixed $state): string => filled($state) ? implode(', ', (array) $state) : '—'),
                    TextEntry::make("{$prefix}trauma_details")->label('Anything you would like to add'),
                ])
                ->columns(2),

            Section::make('Family History')
                ->schema([
                    TextEntry::make("{$prefix}family_description")->label('How would you describe your family?'),
                    TextEntry::make("{$prefix}childhood_experience")->label('Childhood or family experiences'),
                    TextEntry::make("{$prefix}family_support")->label('Support received from family or friends'),
                ])
                ->columns(2),

            Section::make('Counselling / Medical History')
                ->schema([
                    TextEntry::make("{$prefix}counselling_history")->label('Previous counsellor or therapist'),
                    TextEntry::make("{$prefix}diagnosis")->label('Previous mental health diagnosis'),
                    TextEntry::make("{$prefix}therapy_experience")->label('What was helpful or unhelpful'),
                    TextEntry::make("{$prefix}medical_history")->label('Psychiatric services, hospitalisation, or medication'),
                ])
                ->columns(2),

            Section::make('Substance Use')
                ->schema([
                    TextEntry::make("{$prefix}substance_use")->label('Alcohol, tobacco, or other substance use'),
                    TextEntry::make("{$prefix}substance_treatment")->label('Support or treatment received'),
                ])
                ->columns(2),

            Section::make('Legal Issues')
                ->schema([
                    TextEntry::make("{$prefix}legal_issues")->label('Current or past legal issues'),
                    TextEntry::make("{$prefix}legal_impact")->label('How these issues have affected you'),
                ])
                ->columns(2),

            Section::make('Emotional / Mental Wellbeing')
                ->schema([
                    TextEntry::make("{$prefix}emotions")->label('Emotions and mood recently'),
                    TextEntry::make("{$prefix}safety")->label('Thoughts about harming yourself or someone else'),
                    TextEntry::make("{$prefix}support_system")->label('Who you could turn to for support'),
                    TextEntry::make("{$prefix}current_stress")->label('Significant problems or stresses'),
                    TextEntry::make("{$prefix}memory")->label('Memory or concentration concerns'),
                ])
                ->columns(2),

            Section::make('Spirituality / Beliefs')
                ->schema([
                    TextEntry::make("{$prefix}belief_system")->label('Belief system or spiritual practice'),
                    TextEntry::make("{$prefix}spiritual_upbringing")->label('Religion or spiritual practice in upbringing'),
                ])
                ->columns(2),

            Section::make('Reasons for Seeking Counselling')
                ->schema([
                    TextEntry::make("{$prefix}seeking_reason")->label('Concern for which you are seeking counselling'),
                    TextEntry::make("{$prefix}first_occurred")->label('When you first noticed this concern'),
                    TextEntry::make("{$prefix}main_concern")->label('What is most concerning right now'),
                    TextEntry::make("{$prefix}family_concern")->label('Concerns expressed by family or friends'),
                    TextEntry::make("{$prefix}counselling_outcome")->label('What you would like to achieve'),
                    TextEntry::make("{$prefix}anything_else")->label('Anything else you would like to share'),
                ])
                ->columns(2),
        ];
    }
}
