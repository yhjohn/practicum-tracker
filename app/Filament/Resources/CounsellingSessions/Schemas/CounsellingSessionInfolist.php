<?php

namespace App\Filament\Resources\CounsellingSessions\Schemas;

use App\Models\CounsellingSession;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CounsellingSessionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('type')
                    ->badge(),
                TextEntry::make('sessionable.name')
                    ->label('Client / Group'),
                TextEntry::make('session_date')
                    ->date(),
                TextEntry::make('start_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('end_time')
                    ->time()
                    ->placeholder('-'),
                TextEntry::make('duration_minutes')
                    ->label('Duration')
                    ->formatStateUsing(fn(?int $state): string => CounsellingSession::formatMinutesToDuration($state) ?? '-'),
                TextEntry::make('session_notes')
                    ->placeholder('-')
                    ->html()
                    ->columnSpanFull(),
                TextEntry::make('attachments')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
