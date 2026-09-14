<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Filament\Schemas\Intakes\IntakeSummary;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
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
                TextEntry::make('name'),
                TextEntry::make('display_name')
                    ->placeholder('-'),
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
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),

                Section::make('Intake forms')
                    ->columnSpanFull()
                    ->schema([
                        RepeatableEntry::make('intakes')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextEntry::make('status')
                                            ->badge(),
                                        TextEntry::make('submitted_at')
                                            ->dateTime()
                                            ->placeholder('Not yet submitted'),
                                        TextEntry::make('created_at')
                                            ->label('Link generated')
                                            ->dateTime(),
                                    ]),

                                ...collect(IntakeSummary::sections())

                                    ->all(),

                                Section::make('Confidentiality Acknowledgement and Signature')
                                    ->schema([
                                        ImageEntry::make('signature')
                                            ->label('Signature')
                                            ->hiddenLabel()
                                            ->height('8rem')
                                            ->placeholder('Not yet signed'),
                                        TextEntry::make('signature_date')
                                            ->label('Date signed')
                                            ->date()
                                            ->placeholder('-'),
                                    ])
                                    ->collapsible()
                                    ->collapsed(),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Informed Consent')
                    ->columnSpanFull()
                    ->collapsible()
                    ->schema([
                        Grid::make(3)
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
            ]);
    }
}
