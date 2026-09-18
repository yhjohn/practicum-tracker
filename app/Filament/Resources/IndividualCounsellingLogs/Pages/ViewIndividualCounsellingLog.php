<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Pages;

use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use App\Models\IndividualCounsellingLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ViewIndividualCounsellingLog extends ViewRecord
{
    protected static string $resource = IndividualCounsellingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Download report PDF')
                ->icon(Phosphor::FileArrowDown)
                ->visible(fn (IndividualCounsellingLog $record): bool => filled($record->signed_at))
                ->action(fn (IndividualCounsellingLog $record) => response()->streamDownload(
                    fn () => print (Pdf::loadView('pdf.individual-counselling-log', ['log' => $record])->output()),
                    "individual-counselling-report-{$record->client?->name}-{$record->id}.pdf",
                )),
            EditAction::make(),
        ];
    }
}
