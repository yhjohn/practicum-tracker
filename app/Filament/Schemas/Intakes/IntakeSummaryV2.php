<?php

namespace App\Filament\Schemas\Intakes;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class IntakeSummaryV2
{
    /**
     * Read-only sections covering every V2 intake field, shared between the
     * public wizard's review step and the staff-facing client infolist.
     *
     * Grouped to mirror the four steps of the public wizard.
     *
     * @return array<Section>
     */
    public static function sections(string $prefix = ''): array
    {
        $joinList = fn (mixed $state): string => filled($state) ? implode(', ', (array) $state) : '—';

        return [
            Section::make('Personal Information')
                ->schema([
                    TextEntry::make("{$prefix}name")->label('Full name'),
                    TextEntry::make("{$prefix}age"),
                    TextEntry::make("{$prefix}phone"),
                    TextEntry::make("{$prefix}email"),
                    TextEntry::make("{$prefix}marital")->label('Relationship status'),
                    TextEntry::make("{$prefix}occupation"),
                    TextEntry::make("{$prefix}sex"),
                    TextEntry::make("{$prefix}emergency_name")->label("Emergency contact's full name"),
                    TextEntry::make("{$prefix}emergency_relationship")->label('Relationship to you'),
                    TextEntry::make("{$prefix}emergency_phone")->label('Emergency contact phone number'),
                ])
                ->columns(2),

            Section::make('Education, Career & Personal Strengths')
                ->schema([
                    TextEntry::make("{$prefix}education_level")->label('Highest level of education completed'),
                    TextEntry::make("{$prefix}education_level_other")->label('Education (other, specified)'),
                    TextEntry::make("{$prefix}job_satisfaction")->label('Job satisfaction'),
                    TextEntry::make("{$prefix}enjoyable_activities")->label('Activities enjoyed')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}enjoyable_activities_other")->label('Activities (other, specified)'),
                    TextEntry::make("{$prefix}personal_strengths")->label('Personal qualities or strengths')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}personal_strengths_other")->label('Strengths (other, specified)'),
                    TextEntry::make("{$prefix}coping_resources")->label('Activities or resources that help coping')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}coping_resources_other")->label('Coping resources (other, specified)'),
                    TextEntry::make("{$prefix}strengths_to_build")->label('Strengths or support to build on'),
                ])
                ->columns(2),

            Section::make('Family & Social Background')
                ->schema([
                    TextEntry::make("{$prefix}family_relationships")->label('Family relationships')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}family_relationships_other")->label('Family relationships (other, specified)'),
                    TextEntry::make("{$prefix}childhood_experiences")->label('Childhood or family experiences')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}childhood_experiences_other")->label('Childhood experiences (other, specified)'),
                    TextEntry::make("{$prefix}family_support_received")->label('Support received from family or friends'),
                    TextEntry::make("{$prefix}beliefs")->label('Religious, spiritual or personal beliefs')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}beliefs_other")->label('Beliefs (other, specified)'),
                    TextEntry::make("{$prefix}spiritual_upbringing_role")->label('Role of religion/spirituality in upbringing'),
                    TextEntry::make("{$prefix}substances_used")->label('Substances currently used')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}substances_used_other")->label('Substances (other, specified)'),
                    TextEntry::make("{$prefix}substance_use_frequency")->label('Frequency of use'),
                    TextEntry::make("{$prefix}substance_use_frequency_other")->label('Frequency (other, specified)'),
                    TextEntry::make("{$prefix}background_notes")->label('Anything else about background, family, beliefs or support'),
                ])
                ->columns(2),

            Section::make('Counselling / Medical History')
                ->schema([
                    TextEntry::make("{$prefix}previous_counselling")->label('Previously received counselling or therapy'),
                    TextEntry::make("{$prefix}previous_counselling_when")->label('When / main reason for seeking support'),
                    TextEntry::make("{$prefix}mental_health_diagnosis")->label('Previous mental health diagnosis'),
                    TextEntry::make("{$prefix}mental_health_diagnosis_details")->label('Diagnosis specified'),
                    TextEntry::make("{$prefix}psychiatric_services")->label('Psychiatric services, hospitalisation or medication')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}previous_support_feedback")->label('What was helpful or unhelpful'),
                    TextEntry::make("{$prefix}mood_recent")->label('Emotions and mood recently')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}mood_recent_other")->label('Mood (other, specified)'),
                    TextEntry::make("{$prefix}harm_thoughts")->label('Thoughts about harming self or others'),
                    TextEntry::make("{$prefix}harm_thoughts_response")->label('Support needed / response'),
                    TextEntry::make("{$prefix}current_stressors")->label('Current problems or stresses')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}current_stressors_other")->label('Stressors (other, specified)'),
                    TextEntry::make("{$prefix}current_stressors_impact")->label('How much these concerns affect daily life'),
                    TextEntry::make("{$prefix}main_concern")->label('Main concern seeking counselling for'),
                    TextEntry::make("{$prefix}counselling_goals")->label('What you would like to achieve')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}counselling_goals_other")->label('Goals (other, specified)'),
                    TextEntry::make("{$prefix}anything_else")->label('Anything else you would like to share'),
                ])
                ->columns(2),
        ];
    }
}
