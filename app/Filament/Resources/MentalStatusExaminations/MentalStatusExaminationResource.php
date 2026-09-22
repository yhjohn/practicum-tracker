<?php

namespace App\Filament\Resources\MentalStatusExaminations;

use App\Filament\Resources\MentalStatusExaminations\Pages\CreateMentalStatusExamination;
use App\Filament\Resources\MentalStatusExaminations\Pages\EditMentalStatusExamination;
use App\Filament\Resources\MentalStatusExaminations\Pages\ListMentalStatusExaminations;
use App\Filament\Resources\MentalStatusExaminations\Pages\ViewMentalStatusExamination;
use App\Filament\Resources\MentalStatusExaminations\Schemas\MentalStatusExaminationForm;
use App\Filament\Resources\MentalStatusExaminations\Schemas\MentalStatusExaminationInfolist;
use App\Filament\Resources\MentalStatusExaminations\Tables\MentalStatusExaminationsTable;
use App\Models\MentalStatusExamination;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MentalStatusExaminationResource extends Resource
{
    protected static ?string $model = MentalStatusExamination::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|null|\UnitEnum $navigationGroup = 'Client management';

    protected static ?string $navigationLabel = 'MSE';

    protected static ?string $recordTitleAttribute = 'client.name';

    public static function form(Schema $schema): Schema
    {
        return MentalStatusExaminationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MentalStatusExaminationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MentalStatusExaminationsTable::configure($table);
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
            'index' => ListMentalStatusExaminations::route('/'),
            'create' => CreateMentalStatusExamination::route('/create'),
            'view' => ViewMentalStatusExamination::route('/{record}'),
            'edit' => EditMentalStatusExamination::route('/{record}/edit'),
        ];
    }
}
