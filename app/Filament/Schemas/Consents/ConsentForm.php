<?php

namespace App\Filament\Schemas\Consents;

use App\Livewire\PublicConsentForm;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class ConsentForm
{
    /**
     * @return array<mixed>
     */
    public static function schema(PublicConsentForm $livewire): array
    {
        return [
            TextInput::make('full_name')
                ->label('Full Name')
                ->required(),

            Checkbox::make('agree')
                ->label('I have read and understand all the above information and agree to proceed with counselling.')
                ->required(),

            SignaturePad::make('signature')
                ->backgroundColor('#ffffff')
                ->backgroundColorOnDark('#ffffff')
                ->penColor('#000')
                ->penColorOnDark('#000')
                ->label('Digital Signature')
                ->required(),
        ];
    }
}
