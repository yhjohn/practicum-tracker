<?php

namespace App\Filament\Resources\Clients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel()
                    ->prefix('+60 ')
                    ->required(),
                DatePicker::make('dob'),
                TextInput::make('emergency_contact')
                    ->default(null),
                TextInput::make('emergency_contact_phone')
                    ->tel()
                    ->prefix('+60 ')
                    ->default(null),
            ]);
    }
}
