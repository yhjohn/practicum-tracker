<?php

namespace App\Filament\Schemas\Mse;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;

class MseSummary
{
    /**
     * Read-only sections covering every MSE field, shared between the MSE
     * resource's own infolist and the client infolist.
     *
     * @return array<Section>
     */
    public static function sections(string $prefix = ''): array
    {
        $joinList = fn (mixed $state): string => filled($state) ? implode(', ', (array) $state) : '—';

        return [
            Section::make('Client Information')
                ->schema([
                    TextEntry::make("{$prefix}client.name_with_code")->label('Client'),
                    TextEntry::make("{$prefix}client.gender")->label('Gender')->placeholder('-'),
                    TextEntry::make("{$prefix}client.dob")->label('Date of birth')->date()->placeholder('-'),
                    TextEntry::make("{$prefix}client.age")->label('Age')->placeholder('-'),
                    TextEntry::make("{$prefix}date_of_interview")->date()->placeholder('-'),
                ])
                ->columns(2),

            Section::make('General Presentation')
                ->schema([
                    TextEntry::make("{$prefix}dress_grooming")->label('Dress/grooming')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}dress_grooming_other")->label('Dress/grooming (other)')->placeholder('-'),
                    TextEntry::make("{$prefix}motor_function")->label('Gross/fine motor function')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}manual_dexterity")->label('Manual dexterity')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}motor_function_other")->label('Motor function (other)')->placeholder('-'),
                    TextEntry::make("{$prefix}eye_contact")->label('Eye contact')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}eye_contact_other")->label('Eye contact (other)')->placeholder('-'),
                ])
                ->columns(2),

            Section::make('Sensory & Comprehension')
                ->schema([
                    TextEntry::make("{$prefix}vision")->label('Vision')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}vision_detail")->label('Vision (detail)')->placeholder('-'),
                    TextEntry::make("{$prefix}hearing")->label('Hearing')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}hearing_detail")->label('Hearing (detail)')->placeholder('-'),
                    TextEntry::make("{$prefix}verbal_comprehension")->label('Verbal comprehension')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}verbal_comprehension_detail")->label('Verbal comprehension (detail)')->placeholder('-'),
                ])
                ->columns(2),

            Section::make('Orientation')
                ->schema([
                    TextEntry::make("{$prefix}orientation_day_of_week")->label('Day of week')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_month")->label('Month')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_date")->label('Date')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_year")->label('Year')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_season")->label('Season')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_building_type")->label('Type of building')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_floor")->label('Floor')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_town")->label('Town')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_county")->label('County')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_state")->label('State')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_situation_rating")->label('Orientation to situation')->placeholder('-'),
                    TextEntry::make("{$prefix}orientation_situation_notes")->label('Situation notes')->placeholder('-'),
                ])
                ->columns(2),

            Section::make('Speech')
                ->schema([
                    TextEntry::make("{$prefix}speech_fluency")->label('Fluency/flow')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}speech_rate")->label('Rate')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}speech_volume")->label('Volume')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}speech_prosody")->label('Prosody/intonation')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}speech_prosody_other")->label('Prosody (other)')->placeholder('-'),
                ])
                ->columns(2),

            Section::make('Affect, Mood, Sleep & Appetite')
                ->schema([
                    TextEntry::make("{$prefix}affect")->label('Affect')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}mood")->label('Mood')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}mood_other")->label('Mood (other)')->placeholder('-'),
                    TextEntry::make("{$prefix}sleep")->label('Sleep')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}appetite")->label('Appetite')->formatStateUsing($joinList),
                ])
                ->columns(2),

            Section::make('Thought & Motivation')
                ->schema([
                    TextEntry::make("{$prefix}thought_process")->label('Thought process')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}thought_process_other")->label('Thought process (other)')->placeholder('-'),
                    TextEntry::make("{$prefix}thought_content")->label('Thought content')->formatStateUsing($joinList),
                    TextEntry::make("{$prefix}thought_content_delusions_detail")->label('Delusions (detail)')->placeholder('-'),
                    TextEntry::make("{$prefix}thought_content_hallucinations_detail")->label('Hallucinations (detail)')->placeholder('-'),
                    TextEntry::make("{$prefix}thought_content_other_detail")->label('Thought content (other)')->placeholder('-'),
                    TextEntry::make("{$prefix}motivation_effort")->label('Motivation/effort')->formatStateUsing($joinList),
                ])
                ->columns(2),

            Section::make('Clinical Notes')
                ->schema([
                    TextEntry::make("{$prefix}strengths")->placeholder('-')->columnSpanFull(),
                    TextEntry::make("{$prefix}other_observations")->label('Other notable observations')->placeholder('-')->columnSpanFull(),
                ]),
        ];
    }
}
