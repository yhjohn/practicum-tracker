<?php

namespace App\Filament\Resources\PdfDocuments\Schemas;

use App\Models\PdfDocument;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PdfDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title')
                    ->columnSpanFull(),
                TextEntry::make('files')
                    ->label('PDFs')
                    ->state(fn (PdfDocument $record): string => $record->fileLinksHtml())
                    ->html()
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
