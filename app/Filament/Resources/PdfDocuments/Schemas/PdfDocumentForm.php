<?php

namespace App\Filament\Resources\PdfDocuments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PdfDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('files')
                    ->label('PDFs')
                    ->multiple()
                    ->required()
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('pdf-documents')
                    ->visibility('private')
                    ->getUploadedFileNameForStorageUsing(fn (TemporaryUploadedFile $file): string => Str::ulid().'/'.$file->getClientOriginalName())
                    ->downloadable()
                    ->openable()
                    ->columnSpanFull(),
            ]);
    }
}
