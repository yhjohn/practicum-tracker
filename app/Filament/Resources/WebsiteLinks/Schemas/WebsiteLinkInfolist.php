<?php

namespace App\Filament\Resources\WebsiteLinks\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class WebsiteLinkInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('url')
                    ->label('URL')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->color('primary'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
