<?php

namespace App\Filament\Schemas\GroupConsents;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class GroupConsentForm
{
    /**
     * @return array<mixed>
     */
    public static function schema(): array
    {
        return [
            TextInput::make('full_name')
                ->label('Full Name')
                ->required(),

            DatePicker::make('dob')
                ->label('Date of Birth')
                ->native()
                ->maxDate(now())
                ->required(),

            Checkbox::make('agree')
                ->label('I have read and understand all the above information and agree to participate in group counselling.')
                ->required(),

            SignaturePad::make('signature')
                ->backgroundColor('#ffffff')
                ->backgroundColorOnDark('#ffffff')
                ->penColor('#000')
                ->penColorOnDark('#000')
                ->label('Digital Signature')
                ->required(),

            Section::make('Parent / Guardian')
                ->description('Only required for clients under the age of 18.')
                ->collapsible()
                ->schema([
                    TextInput::make('guardian_name')
                        ->label('Full Name'),

                    TextInput::make('guardian_relationship')
                        ->label('Relationship to Client'),

                    SignaturePad::make('guardian_signature')
                        ->backgroundColor('#ffffff')
                        ->backgroundColorOnDark('#ffffff')
                        ->penColor('#000')
                        ->penColorOnDark('#000')
                        ->label('Parent / Guardian Signature'),
                ]),
        ];
    }
}
