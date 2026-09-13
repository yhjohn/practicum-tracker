<?php

namespace App\Filament\Resources\CounsellingSessions;

use App\Filament\Resources\CounsellingSessions\Pages\CreateCounsellingSession;
use App\Filament\Resources\CounsellingSessions\Pages\EditCounsellingSession;
use App\Filament\Resources\CounsellingSessions\Pages\ListCounsellingSessions;
use App\Filament\Resources\CounsellingSessions\Pages\ViewCounsellingSession;
use App\Filament\Resources\CounsellingSessions\Schemas\CounsellingSessionForm;
use App\Filament\Resources\CounsellingSessions\Schemas\CounsellingSessionInfolist;
use App\Filament\Resources\CounsellingSessions\Tables\CounsellingSessionsTable;
use App\Models\CounsellingSession;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CounsellingSessionResource extends Resource
{
    protected static ?string $model = CounsellingSession::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CounsellingSessionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CounsellingSessionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CounsellingSessionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCounsellingSessions::route('/'),
            'create' => CreateCounsellingSession::route('/create'),
            'view' => ViewCounsellingSession::route('/{record}'),
            'edit' => EditCounsellingSession::route('/{record}/edit'),
        ];
    }
}
