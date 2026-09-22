<?php

namespace App\Filament\Resources\MentalStatusExaminations\Pages;

use App\Filament\Resources\MentalStatusExaminations\MentalStatusExaminationResource;
use App\Models\MentalStatusExamination;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ViewMentalStatusExamination extends ViewRecord
{
    protected static string $resource = MentalStatusExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Download PDF')
                ->icon(Phosphor::FileArrowDown)
                ->action(fn (MentalStatusExamination $record) => response()->streamDownload(
                    fn () => print (Pdf::loadView('pdf.mental-status-examination', ['mse' => $record])->output()),
                    "mse-{$record->client?->name}-{$record->id}.pdf",
                )),
            EditAction::make(),
        ];
    }
}
