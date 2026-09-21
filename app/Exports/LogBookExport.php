<?php

namespace App\Exports;

use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use App\Models\LogBookEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithExportTemplate;
use Maatwebsite\Excel\Events\BeforeWriting;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Fills the practicum log book XLSX template (resources/templates/practicum-log-book.xlsx).
 * The SUMMARY sheet is all formulas over the weekly sheets, so only the cover,
 * client tracker and weekly sheets are written.
 */
class LogBookExport implements Export, WithEvents, WithExportTemplate
{
    use Exportable;

    private const FIRST_ENTRY_ROW = 5;

    private const LAST_ENTRY_ROW = 19;

    private const TRACKER_FIRST_ROW = 5;

    private const TRACKER_CLIENT_SLOTS = 10;

    private const TRACKER_SESSION_SLOTS = 4;

    /**
     * @var Collection<int, LogBookEntry>|null
     */
    private ?Collection $entries = null;

    /**
     * Anything the template cannot hold, so the export can warn instead of silently dropping it.
     *
     * @return array<int, string>
     */
    public function warnings(): array
    {
        $warnings = [];
        $slots = self::LAST_ENTRY_ROW - self::FIRST_ENTRY_ROW + 1;

        $outOfRange = $this->entries()->filter(fn (LogBookEntry $entry): bool => $entry->week === null || $entry->week > config('practicum.weeks'));

        if ($outOfRange->isNotEmpty()) {
            $warnings[] = "{$outOfRange->count()} entries fall outside weeks 1–".config('practicum.weeks').' and were left out.';
        }

        foreach ($this->entries()->groupBy('week') as $week => $entries) {
            if ($week !== '' && $week <= config('practicum.weeks') && $entries->count() > $slots) {
                $warnings[] = "Week {$week} has {$entries->count()} entries but the template holds {$slots}; the extra ones (and their hours) were left out.";
            }
        }

        return $warnings;
    }

    public function exportTemplate(): string
    {
        return resource_path('templates/practicum-log-book.xlsx');
    }

    public function registerEvents(): array
    {
        return [
            BeforeWriting::class => fn (BeforeWriting $event) => $this->fill($event->writer->getDelegate()),
        ];
    }

    private function fill(Spreadsheet $spreadsheet): void
    {
        $this->fillCover($spreadsheet->getSheetByNameOrThrow('COVER'));
        $this->fillClientTracker($spreadsheet->getSheetByNameOrThrow('CLIENT TRACKER'));

        $byWeek = $this->entries()->groupBy('week');

        foreach (range(1, config('practicum.weeks')) as $week) {
            $this->fillWeek(
                $spreadsheet->getSheetByNameOrThrow(sprintf('W%02d', $week)),
                $week,
                $byWeek->get($week, collect()),
            );
        }
    }

    private function fillCover(Worksheet $sheet): void
    {
        $student = config('practicum.student');

        foreach ([
            'C7' => 'name',
            'C9' => 'matriculation_no',
            'C11' => 'semester',
            'C13' => 'site_supervisor',
            'C15' => 'academic_supervisor',
            'C17' => 'organization',
            'C19' => 'site_address',
        ] as $cell => $key) {
            $sheet->setCellValue($cell, $student[$key]);
        }
    }

    /**
     * One row per client with individual sessions, first sessions first.
     * ponytail: the template has 10 client rows x 4 session columns; extras are left out.
     */
    private function fillClientTracker(Worksheet $sheet): void
    {
        $sessionsByClient = IndividualCounsellingLog::query()
            ->whereNotNull('session_date')
            ->orderBy('session_date')
            ->get()
            ->groupBy('client_id')
            ->take(self::TRACKER_CLIENT_SLOTS);

        $clients = Client::with(['intake', 'intakeV2'])->findMany($sessionsByClient->keys())->keyBy('id');

        for ($slot = 0; $slot < self::TRACKER_CLIENT_SLOTS; $slot++) {
            $row = self::TRACKER_FIRST_ROW + $slot;
            $sessions = $sessionsByClient->values()->get($slot);
            $client = $sessions ? $clients->get($sessions->first()->client_id) : null;

            $sheet->setCellValue("B{$row}", $client?->name);
            $sheet->setCellValue("C{$row}", $client?->age());
            $sheet->setCellValue("D{$row}", $client?->gender());

            foreach (range(0, self::TRACKER_SESSION_SLOTS - 1) as $sessionSlot) {
                $sheet->setCellValue(
                    chr(ord('E') + $sessionSlot).$row,
                    $sessions?->get($sessionSlot)?->session_date ? Date::PHPToExcel($sessions->get($sessionSlot)->session_date) : null,
                );
            }
        }
    }

    /**
     * @param  Collection<int, LogBookEntry>  $entries
     */
    private function fillWeek(Worksheet $sheet, int $week, Collection $entries): void
    {
        $from = LogBookEntry::weekStart($week);

        $sheet->setCellValue('F2', Date::PHPToExcel($from));
        $sheet->setCellValue('I2', Date::PHPToExcel($from->copy()->addDays(6)));

        $rows = $entries->take(self::LAST_ENTRY_ROW - self::FIRST_ENTRY_ROW + 1)->values();

        for ($row = self::FIRST_ENTRY_ROW; $row <= self::LAST_ENTRY_ROW; $row++) {
            $entry = $rows->get($row - self::FIRST_ENTRY_ROW);

            $sheet->setCellValue("B{$row}", $entry ? Date::PHPToExcel($entry->session_date) : null);
            $sheet->setCellValue("C{$row}", $entry?->timeRange());
            $sheet->setCellValue("D{$row}", $entry?->description);
            $sheet->setCellValue("E{$row}", $entry?->activity->getLabel());
            $sheet->setCellValue("F{$row}", $entry?->client_code);
            $sheet->setCellValue("G{$row}", $entry?->number_of_clients);
            $sheet->setCellValue("H{$row}", $entry ? (float) $entry->duration_hours : null);
            $sheet->setCellValue("I{$row}", $entry?->notes);
        }
    }

    /**
     * @return Collection<int, LogBookEntry>
     */
    private function entries(): Collection
    {
        return $this->entries ??= LogBookEntry::query()
            ->orderBy('session_date')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();
    }
}
