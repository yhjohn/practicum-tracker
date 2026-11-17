<?php

namespace App\Exports;

use App\Filament\Concerns\ExportsReportDocx;
use App\Models\Client;
use App\Models\ProgressJourney;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpWord\TemplateProcessor;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Fills the Individual Counselling Client Progress Summary Form
 * (resources/templates/progress-journey.docx). Each client's progress journey
 * entries are laid out three sessions per block: a session header row followed
 * by one row per component.
 */
class ProgressJourneyExport
{
    use ExportsReportDocx;

    /**
     * Column widths (twips) copied from the original form.
     */
    private const COLUMN_WIDTHS = [2588, 2588, 2587, 2589, 2588];

    private const SESSIONS_PER_BLOCK = 3;

    /**
     * @var array<string, string> progress journey attribute => row label
     */
    private const COMPONENTS = [
        'background' => 'Background of the Presenting Issues',
        'case_conceptualisation' => 'Case Conceptualisation',
        'interventions' => 'Interventions',
        'progress_summary' => 'Client Progress Summary',
    ];

    /**
     * @param  Collection<int, Client>  $clients
     */
    public static function download(Collection $clients, string $name): BinaryFileResponse
    {
        return response()->download(static::generate($clients, $name), "{$name}.docx");
    }

    /**
     * @param  Collection<int, Client>  $clients
     * @return string path to the stored DOCX
     */
    public static function generate(Collection $clients, string $key): string
    {
        $clients->load('progressJourneys.individualCounsellingLog');

        static::$richStyle = ['font' => 'Arial', 'paragraphStyle' => 'Body', 'baseIndent' => 0];

        $processor = new TemplateProcessor(resource_path('templates/progress-journey.docx'));
        $processor->replaceXmlBlock('progress_table', static::tableXml($clients));

        return static::storeDocx($processor, $key);
    }

    /**
     * @param  Collection<int, Client>  $clients
     */
    protected static function tableXml(Collection $clients): string
    {
        $rows = '';
        $isFirstRow = true;

        foreach ($clients->values() as $clientIndex => $client) {
            $blocks = $client->progressJourneys
                ->sortBy('individualCounsellingLog.session_number')
                ->values()
                ->chunk(self::SESSIONS_PER_BLOCK);

            foreach ($blocks as $blockIndex => $block) {
                $block = $block->values();

                $headerCells = [
                    static::boldParagraph($isFirstRow ? 'Client Code' : ''),
                    static::boldParagraph($isFirstRow ? 'Component / Information' : ''),
                ];

                for ($slot = 0; $slot < self::SESSIONS_PER_BLOCK; $slot++) {
                    $headerCells[] = static::sessionHeading($block->get($slot));
                }

                $rows .= static::row($headerCells, cantSplit: true);
                $isFirstRow = false;

                foreach (array_keys(self::COMPONENTS) as $componentIndex => $attribute) {
                    $clientCell = $blockIndex === 0 ? static::boldParagraph($componentIndex === 0 ? 'Client '.($clientIndex + 1)." ({$client->client_code})" : '') : static::wordParagraph('');
                    $cells = [$clientCell, static::boldParagraph(self::COMPONENTS[$attribute])];

                    for ($slot = 0; $slot < self::SESSIONS_PER_BLOCK; $slot++) {
                        $cells[] = static::htmlToWordXml($block->get($slot)?->{$attribute});
                    }

                    $clientCellMerge = $blockIndex === 0 ? ($componentIndex === 0 ? 'restart' : 'continue') : null;
                    $rows .= static::row($cells, $clientCellMerge);
                }
            }
        }

        $border = fn (string $side): string => '<w:'.$side.' w:val="single" w:color="000000" w:sz="8" w:space="0"/>';
        $margin = fn (string $side): string => '<w:'.$side.' w:w="80" w:type="dxa"/>';

        return '<w:tbl><w:tblPr>'
            .'<w:tblW w:w="'.array_sum(self::COLUMN_WIDTHS).'" w:type="dxa"/>'
            .'<w:tblInd w:w="108" w:type="dxa"/>'
            .'<w:tblBorders>'.implode('', array_map($border, ['top', 'left', 'bottom', 'right', 'insideH', 'insideV'])).'</w:tblBorders>'
            .'<w:tblLayout w:type="fixed"/>'
            .'<w:tblCellMar>'.implode('', array_map($margin, ['top', 'left', 'bottom', 'right'])).'</w:tblCellMar>'
            .'</w:tblPr><w:tblGrid>'
            .implode('', array_map(fn (int $width): string => '<w:gridCol w:w="'.$width.'"/>', self::COLUMN_WIDTHS))
            .'</w:tblGrid>'.$rows.'</w:tbl>';
    }

    /**
     * @param  list<string>  $cells  paragraph XML per column
     * @param  ?string  $clientCellMerge  vMerge value for the first (client) column
     */
    protected static function row(array $cells, ?string $clientCellMerge = null, bool $cantSplit = false): string
    {
        $xml = '';

        foreach ($cells as $column => $paragraphs) {
            $merge = $column === 0 && $clientCellMerge ? '<w:vMerge w:val="'.$clientCellMerge.'"/>' : '';

            $xml .= '<w:tc><w:tcPr><w:tcW w:w="'.self::COLUMN_WIDTHS[$column].'" w:type="dxa"/>'.$merge.'</w:tcPr>'.$paragraphs.'</w:tc>';
        }

        return '<w:tr>'.($cantSplit ? '<w:trPr><w:cantSplit/></w:trPr>' : '').$xml.'</w:tr>';
    }

    protected static function sessionHeading(?ProgressJourney $journey): string
    {
        if (! $journey) {
            return static::wordParagraph('');
        }

        $log = $journey->individualCounsellingLog;
        $lineBreak = '<w:r><w:br/></w:r>';

        return static::wordParagraph(
            static::wordRun("Session {$log->session_number}", ['b' => true]).$lineBreak
            .static::wordRun('Date: '.($log->session_date?->format('j F Y') ?? ''), ['b' => true]).$lineBreak
            .static::wordRun('Time: '.($log->start_time?->format('H:i') ?? ''), ['b' => true]),
        );
    }

    protected static function boldParagraph(string $text): string
    {
        return static::wordParagraph($text === '' ? '' : static::wordRun($text, ['b' => true]));
    }
}
