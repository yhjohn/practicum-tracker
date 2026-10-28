<?php

namespace App\Filament\Resources\PdfDocuments\Pages;

use App\Filament\Resources\PdfDocuments\PdfDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPdfDocument extends ViewRecord
{
    protected static string $resource = PdfDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
