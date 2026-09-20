<?php

namespace App\Filament\Resources\GroupCounsellingLogs\Pages;

use App\Filament\Resources\GroupCounsellingLogs\GroupCounsellingLogResource;
use App\Filament\Resources\IndividualCounsellingLogs\Pages\ViewIndividualCounsellingLog;
use App\Models\GroupCounsellingLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use PhpOffice\PhpWord\TemplateProcessor;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class ViewGroupCounsellingLog extends ViewRecord
{
    protected static string $resource = GroupCounsellingLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Download report PDF')
                ->icon(Phosphor::FileArrowDown)
                ->action(fn (GroupCounsellingLog $record) => response()->streamDownload(
                    fn () => print (Pdf::loadView('pdf.group-counselling-log', ['log' => $record])->output()),
                    "group-counselling-report-{$record->group?->name}-{$record->id}.pdf",
                )),
            Action::make('downloadDocx')
                ->label('Download report DOCX')
                ->icon(Phosphor::FileArrowDown)
                ->action(fn (GroupCounsellingLog $record) => static::downloadDocx($record)),
            EditAction::make(),
        ];
    }

    protected static function downloadDocx(GroupCounsellingLog $record): mixed
    {
        $templatePath = resource_path('templates/group-counselling-log.docx');

        if (! is_file($templatePath)) {
            Notification::make()
                ->danger()
                ->title('DOCX template missing')
                ->body('Add the template to resources/templates/group-counselling-log.docx first.')
                ->send();

            return null;
        }

        $processor = new TemplateProcessor($templatePath);
        $plain = ViewIndividualCounsellingLog::htmlToPlainText(...);

        $processor->setValues([
            'group_name' => $record->group?->name ?? '',
            'session_number' => (string) ($record->session_number ?? ''),
            'session_theme' => $record->session_theme ?? '',
            'counselling_approach' => $record->counselling_approach ?? '',
            'session_date' => optional($record->session_date)->format('d M Y') ?? '',
            'time' => optional($record->start_time)->format('H:i') ?? '',
            'duration' => $record->duration_hours ? "{$record->duration_hours} hrs" : '',
            'place' => $record->place ?? '',
            'total_sessions_planned' => (string) ($record->total_sessions_planned ?? ''),
            'group_size' => (string) count($record->members ?? []),
            'facilitator_name' => $record->facilitator_name ?? '',
            'supervisor_name' => $record->supervisor_name ?? '',
            'part1' => $plain($record->part1_subjective_objective),
            'part2' => $plain($record->part2_assessment),
            'part3' => $plain($record->part3_treatment_plan),
            'part4' => $plain($record->part4_evaluation),
            'written_at' => optional($record->written_at)->format('d/m/Y H:i') ?? '',
        ]);

        $rows = $record->memberRows();

        foreach (['member_name' => 'member', 'progress_name' => 'progress'] as $anchor => $prefix) {
            if ($rows->isEmpty()) {
                $processor->deleteRow($anchor);

                continue;
            }

            $processor->cloneRow($anchor, $rows->count());

            foreach ($rows as $i => $row) {
                $n = $i + 1;
                $processor->setValues($prefix === 'member' ? [
                    "member_no#{$n}" => (string) $row['no'],
                    "member_name#{$n}" => $row['name'],
                    "member_gender#{$n}" => $row['gender'],
                    "member_age#{$n}" => $row['age'],
                    "member_attendance#{$n}" => $row['attendance'],
                ] : [
                    "progress_no#{$n}" => (string) $row['no'],
                    "progress_name#{$n}" => $row['name'],
                    "progress_level#{$n}" => $row['participation'],
                    "progress_notes#{$n}" => $row['progress_notes'],
                ]);
            }
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'gcl_').'.docx';
        $processor->saveAs($tempPath);

        return response()->download(
            $tempPath,
            "group-counselling-report-{$record->group?->name}-{$record->id}.docx",
        )->deleteFileAfterSend();
    }
}
