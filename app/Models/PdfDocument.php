<?php

namespace App\Models;

use Database\Factories\PdfDocumentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PdfDocument extends Model
{
    /** @use HasFactory<PdfDocumentFactory> */
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'files' => 'array',
    ];

    /**
     * Each stored PDF as a short-lived link that opens in a new tab.
     */
    public function fileLinksHtml(): string
    {
        return collect($this->files)
            ->map(fn (string $path): string => '<a href="'.e(Storage::disk(config('filament.default_filesystem_disk'))->temporaryUrl($path, now()->addMinutes(10))).'" target="_blank" class="text-primary-600 underline">'.e(basename($path)).'</a>')
            ->implode('<br>');
    }
}
