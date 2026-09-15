<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Filament\Schemas\Intakes\IntakeSummary;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ClientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Information')
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email')
                            ->label('Email address'),
                        TextEntry::make('phone'),
                        TextEntry::make('dob')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('emergency_contact')
                            ->placeholder('-'),
                        TextEntry::make('emergency_contact_phone')
                            ->placeholder('-'),

                        Section::make('Informed Consent')
                            ->schema([
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('consent.status')
                                            ->label('Status')
                                            ->badge()
                                            ->placeholder('Not yet generated'),
                                        TextEntry::make('consent.full_name')
                                            ->label('Full name')
                                            ->placeholder('-'),
                                        TextEntry::make('consent.submitted_at')
                                            ->label('Signed at')
                                            ->dateTime()
                                            ->placeholder('Not yet submitted'),
                                    ]),
                                ImageEntry::make('consent.signature')
                                    ->label('Signature')
                                    ->hiddenLabel()
                                    ->height('8rem')
                                    ->placeholder('Not yet signed'),
                            ]),
                    ])->columnSpanFull(),

                Section::make('Intake forms')

                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('intake.status')
                                    ->label('Status')
                                    ->badge()
                                    ->placeholder('Not yet generated'),
                                TextEntry::make('intake.submitted_at')
                                    ->dateTime()
                                    ->placeholder('Not yet submitted'),
                                TextEntry::make('intake.created_at')
                                    ->label('Link generated')
                                    ->dateTime()
                                    ->placeholder('-'),
                            ]),

                        ...collect(IntakeSummary::sections('intake.'))
                            ->all(),
                    ])
                    ->columnSpanFull()
                    ->collapsible(),

            ]);
    }
}
