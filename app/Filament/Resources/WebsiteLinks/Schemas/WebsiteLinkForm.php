<?php

namespace App\Filament\Resources\WebsiteLinks\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WebsiteLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('url')
                    ->label('URL')
                    ->url()
                    ->required(),
            ]);
    }
}
