<?php

namespace App\Filament\Concerns;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

trait ExportsReportDocx
{
    /**
     * Save the report to a stable path (same key = same file, overwritten
     * on every export) so repeat downloads always reuse the same location.
     */
    protected static function storeDocx(TemplateProcessor $processor, string $key): string
    {
        Storage::disk('local')->makeDirectory('exports');

        $path = Storage::disk('local')->path("exports/{$key}.docx");

        $processor->saveAs($path);

        return $path;
    }

    protected static function convertDocxToPdf(string $docxPath): ?string
    {
        $response = Http::attach('files', file_get_contents($docxPath), basename($docxPath))
            ->post(config('services.gotenberg.url').'/forms/libreoffice/convert');

        if ($response->failed()) {
            Notification::make()
                ->danger()
                ->title('PDF conversion failed')
                ->body('Gotenberg returned an error converting the report to PDF.')
                ->send();

            return null;
        }

        return $response->body();
    }
}
