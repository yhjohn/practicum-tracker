<?php

namespace App\Filament\Resources\GroupCounsellingLogs;

use App\Filament\Resources\GroupCounsellingLogs\Pages\CreateGroupCounsellingLog;
use App\Filament\Resources\GroupCounsellingLogs\Pages\EditGroupCounsellingLog;
use App\Filament\Resources\GroupCounsellingLogs\Pages\ListGroupCounsellingLogs;
use App\Filament\Resources\GroupCounsellingLogs\Pages\ViewGroupCounsellingLog;
use App\Filament\Resources\GroupCounsellingLogs\Schemas\GroupCounsellingLogForm;
use App\Filament\Resources\GroupCounsellingLogs\Schemas\GroupCounsellingLogInfolist;
use App\Filament\Resources\GroupCounsellingLogs\Tables\GroupCounsellingLogsTable;
use App\Models\GroupCounsellingLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GroupCounsellingLogResource extends Resource
{
    protected static ?string $model = GroupCounsellingLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return GroupCounsellingLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return GroupCounsellingLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupCounsellingLogsTable::configure($table);
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
            'index' => ListGroupCounsellingLogs::route('/'),
            'create' => CreateGroupCounsellingLog::route('/create'),
            'view' => ViewGroupCounsellingLog::route('/{record}'),
            'edit' => EditGroupCounsellingLog::route('/{record}/edit'),
        ];
    }
}
