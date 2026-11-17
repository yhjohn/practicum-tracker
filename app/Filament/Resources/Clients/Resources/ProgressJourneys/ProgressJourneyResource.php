<?php

namespace App\Filament\Resources\Clients\Resources\ProgressJourneys;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Clients\Resources\ProgressJourneys\Pages\CreateProgressJourney;
use App\Filament\Resources\Clients\Resources\ProgressJourneys\Pages\EditProgressJourney;
use App\Filament\Resources\Clients\Resources\ProgressJourneys\Schemas\ProgressJourneyForm;
use App\Filament\Resources\Clients\Resources\ProgressJourneys\Tables\ProgressJourneysTable;
use App\Models\ProgressJourney;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ProgressJourneyResource extends Resource
{
    protected static ?string $model = ProgressJourney::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Path;

    protected static ?string $parentResource = ClientResource::class;

    public static function form(Schema $schema): Schema
    {
        return ProgressJourneyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProgressJourneysTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'create' => CreateProgressJourney::route('/create'),
            'edit' => EditProgressJourney::route('/{record}/edit'),
        ];
    }
}
