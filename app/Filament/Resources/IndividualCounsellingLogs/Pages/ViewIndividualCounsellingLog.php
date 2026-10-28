<?php

namespace App\Filament\Resources\IndividualCounsellingLogs\Pages;

use App\Filament\Concerns\ExportsReportDocx;
use App\Filament\Resources\IndividualCounsellingLogs\IndividualCounsellingLogResource;
use App\Models\IndividualCounsellingLog;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use PhpOffice\PhpWord\TemplateProcessor;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ViewIndividualCounsellingLog extends ViewRecord
{
    use ExportsReportDocx;

    protected static string $resource = IndividualCounsellingLogResource::class;

    protected static ?string $title = 'Individual Counselling Log';

    protected Width|string|null $maxContentWidth = Width::SixExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('v1 PDF')
                ->icon(Phosphor::FileArrowDown)
                ->action(fn (IndividualCounsellingLog $record) => static::downloadPdf($record)),
            Action::make('downloadDocx')
                ->label('v1 DOCX')
                ->icon(Phosphor::FileArrowDown)
                ->action(fn (IndividualCounsellingLog $record) => static::downloadDocx($record)),
            Action::make('downloadPdfV2')
                ->label('v2 PDF')
                ->icon(Phosphor::FileArrowDown)
                ->action(fn (IndividualCounsellingLog $record) => static::downloadPdf($record, 'v2')),
            Action::make('downloadDocxV2')
                ->label('v2 DOCX')
                ->icon(Phosphor::FileArrowDown)
                ->action(fn (IndividualCounsellingLog $record) => static::downloadDocx($record, 'v2')),
            EditAction::make(),
        ];
    }

    protected static function downloadDocx(IndividualCounsellingLog $record, string $version = 'v1'): mixed
    {
        $path = static::generateDocx($record, $version);

        if (! $path) {
            return null;
        }

        return response()->download($path, static::downloadName($record, $version).'.docx');
    }

    protected static function downloadPdf(IndividualCounsellingLog $record, string $version = 'v1'): mixed
    {
        $docxPath = static::generateDocx($record, $version);

        if (! $docxPath) {
            return null;
        }

        $pdf = static::convertDocxToPdf($docxPath);

        if (! $pdf) {
            return null;
        }

        return response()->streamDownload(
            fn () => print ($pdf),
            static::downloadName($record, $version).'.pdf',
        );
    }

    protected static function downloadName(IndividualCounsellingLog $record, string $version): string
    {
        $suffix = $version === 'v1' ? '' : "-{$version}";

        return "individual-counselling-report{$suffix}-{$record->client?->client_code} SESSION {$record->session_number}";
    }

    /**
     * Rebuilds and stores the DOCX from the version's template, always fresh,
     * at a path keyed by version and record id so repeat exports reuse the
     * same file.
     */
    protected static function generateDocx(IndividualCounsellingLog $record, string $version = 'v1'): ?string
    {
        $isV2 = $version === 'v2';
        $templateName = $isV2 ? 'Individual Progress Notes.docx' : 'individual-counselling-log.docx';
        $templatePath = resource_path("templates/{$templateName}");

        if (! is_file($templatePath)) {
            Notification::make()
                ->danger()
                ->title('DOCX template missing')
                ->body("Add the template to resources/templates/{$templateName} first.")
                ->send();

            return null;
        }

        $processor = new TemplateProcessor($templatePath);

        $processor->setValues([
            'client_name' => $record->client?->client_code ?? '',
            'session_date' => optional($record->session_date)->format($isV2 ? 'j F Y' : 'd M Y') ?? '',
            'session_number' => (string) ($record->session_number ?? ''),
            'time' => optional($record->start_time)->format('H:i') ?? '',
            'duration' => $record->duration_hours ? "{$record->duration_hours} hrs" : '',
            'place' => $record->place ?? '',
            'written_at' => optional($record->written_at)->format('d/m/Y H:i') ?? '',
        ]);

        if ($isV2) {
            $processor->setValues([
                'age' => $record->client?->age ?? '',
                'gender' => $record->client?->gender ?? '',
                'occupation' => $record->client?->intakeV2?->occupation ?? $record->client?->intake?->occupation ?? '',
                'student_name' => auth()->user()?->name ?? '',
                'referral_source' => $record->referral_source ?? '',
                'counselling_approach' => $record->counselling_approach ?? '',
                'total_sessions' => (string) ($record->total_sessions_planned ?? ''),
            ]);
        }

        foreach ([
            'part1' => $record->part1_subjective_objective,
            'part2' => $record->part2_assessment,
            'part3' => $record->part3_treatment_plan,
            'part4' => $record->part4_evaluation,
        ] as $macro => $html) {
            $isV2
                ? static::setRichText($processor, $macro, $html, 'Arial', null, 360)
                : static::setRichText($processor, $macro, $html);
        }

        static::fillSignature($processor);

        return static::storeDocx($processor, $isV2 ? "individual-counselling-log-v2-{$record->id}" : "individual-counselling-log-{$record->id}");
    }

    public static function fillSignature(TemplateProcessor $processor): void
    {
        $path = resource_path('templates/signature.png');

        if (! is_file($path)) {
            $processor->setValue('signature', '');

            return;
        }

        $processor->setImageValue('signature', ['path' => $path, 'width' => 120, 'height' => 95]);
    }
}
