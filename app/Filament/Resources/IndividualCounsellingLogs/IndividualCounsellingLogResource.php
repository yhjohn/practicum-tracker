<?php

namespace App\Filament\Resources\IndividualCounsellingLogs;

use App\Filament\Resources\IndividualCounsellingLogs\Pages\CreateIndividualCounsellingLog;
use App\Filament\Resources\IndividualCounsellingLogs\Pages\EditIndividualCounsellingLog;
use App\Filament\Resources\IndividualCounsellingLogs\Pages\ListIndividualCounsellingLogs;
use App\Filament\Resources\IndividualCounsellingLogs\Pages\ViewIndividualCounsellingLog;
use App\Filament\Resources\IndividualCounsellingLogs\Schemas\IndividualCounsellingLogForm;
use App\Filament\Resources\IndividualCounsellingLogs\Schemas\IndividualCounsellingLogInfolist;
use App\Filament\Resources\IndividualCounsellingLogs\Tables\IndividualCounsellingLogsTable;
use App\Models\IndividualCounsellingLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IndividualCounsellingLogResource extends Resource
{
    protected static ?string $model = IndividualCounsellingLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    public static function form(Schema $schema): Schema
    {
        return IndividualCounsellingLogForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return IndividualCounsellingLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IndividualCounsellingLogsTable::configure($table);
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
            'index' => ListIndividualCounsellingLogs::route('/'),
            'create' => CreateIndividualCounsellingLog::route('/create'),
            'view' => ViewIndividualCounsellingLog::route('/{record}'),
            'edit' => EditIndividualCounsellingLog::route('/{record}/edit'),
        ];
    }
}
