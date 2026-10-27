<?php

namespace App\Filament\Resources\WebsiteLinks;

use App\Filament\Resources\WebsiteLinks\Pages\ListWebsiteLinks;
use App\Filament\Resources\WebsiteLinks\Pages\ViewWebsiteLink;
use App\Filament\Resources\WebsiteLinks\Schemas\WebsiteLinkForm;
use App\Filament\Resources\WebsiteLinks\Schemas\WebsiteLinkInfolist;
use App\Filament\Resources\WebsiteLinks\Tables\WebsiteLinksTable;
use App\Models\WebsiteLink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class WebsiteLinkResource extends Resource
{
    protected static ?string $model = WebsiteLink::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::Link;

    protected static string|null|\UnitEnum $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return WebsiteLinkForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WebsiteLinkInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebsiteLinksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebsiteLinks::route('/'),
            'view' => ViewWebsiteLink::route('/{record}'),
        ];
    }
}
