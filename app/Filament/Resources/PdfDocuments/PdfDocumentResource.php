<?php

namespace App\Filament\Resources\PdfDocuments;

use App\Filament\Resources\PdfDocuments\Pages\ListPdfDocuments;
use App\Filament\Resources\PdfDocuments\Pages\ViewPdfDocument;
use App\Filament\Resources\PdfDocuments\Schemas\PdfDocumentForm;
use App\Filament\Resources\PdfDocuments\Schemas\PdfDocumentInfolist;
use App\Filament\Resources\PdfDocuments\Tables\PdfDocumentsTable;
use App\Models\PdfDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class PdfDocumentResource extends Resource
{
    protected static ?string $model = PdfDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Phosphor::FilePdf;

    protected static string|null|\UnitEnum $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'PDF';

    protected static ?string $navigationLabel = 'PDFs';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return PdfDocumentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PdfDocumentInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PdfDocumentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPdfDocuments::route('/'),
            'view' => ViewPdfDocument::route('/{record}'),
        ];
    }
}
