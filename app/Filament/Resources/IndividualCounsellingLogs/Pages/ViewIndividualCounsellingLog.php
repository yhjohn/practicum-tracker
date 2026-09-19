<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Pages;

use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use App\Models\IndividualCounsellingLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use PhpOffice\PhpWord\TemplateProcessor;
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
                ->action(fn (IndividualCounsellingLog $record) => response()->streamDownload(
                    fn () => print (Pdf::loadView('pdf.individual-counselling-log', ['log' => $record])->output()),
                    "individual-counselling-report-{$record->client?->name}-{$record->id}.pdf",
                )),
            Action::make('downloadDocx')
                ->label('Download report DOCX')
                ->icon(Phosphor::FileArrowDown)
                ->action(fn (IndividualCounsellingLog $record) => static::downloadDocx($record)),
            EditAction::make(),
        ];
    }

    protected static function downloadDocx(IndividualCounsellingLog $record): mixed
    {
        $templatePath = resource_path('templates/individual-counselling-log.docx');

        if (! is_file($templatePath)) {
            Notification::make()
                ->danger()
                ->title('DOCX template missing')
                ->body('Add the template to resources/templates/individual-counselling-log.docx first.')
                ->send();

            return null;
        }

        $processor = new TemplateProcessor($templatePath);

        $processor->setValues([
            'client_name' => $record->client?->name ?? '',
            'session_date' => optional($record->session_date)->format('d M Y') ?? '',
            'session_number' => (string) ($record->session_number ?? ''),
            'time' => optional($record->start_time)->format('H:i') ?? '',
            'duration' => $record->duration_hours ? "{$record->duration_hours} hrs" : '',
            'place' => $record->place ?? '',
            'part1' => static::htmlToPlainText($record->part1_subjective_objective),
            'part2' => static::htmlToPlainText($record->part2_assessment),
            'part3' => static::htmlToPlainText($record->part3_treatment_plan),
            'part4' => static::htmlToPlainText($record->part4_evaluation),
            'written_at' => optional($record->written_at)->format('d/m/Y H:i') ?? '',
        ]);

        $tempPath = tempnam(sys_get_temp_dir(), 'icl_').'.docx';
        $processor->saveAs($tempPath);

        return response()->download(
            $tempPath,
            "individual-counselling-report-{$record->client?->name}-{$record->id}.docx",
        )->deleteFileAfterSend();
    }

    /**
     * RichEditor stores HTML; the docx template only takes plain text, so
     * paragraph/line breaks become "\n" (PhpWord's setValue() turns those
     * into <w:br/> automatically) and every other tag is stripped.
     */
    protected static function htmlToPlainText(?string $html): string
    {
        if (blank($html)) {
            return '';
        }

        $text = preg_replace('/<(br|\/p|\/li)\s*\/?>/i', "\n", $html);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES);

        return trim($text);
    }
}
