<?php

namespace App\Jobs;

use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Turns an exported individual counselling report PDF back into a log by having
 * `claude -p` transcribe it. Runs on the "summaries" queue, because only the
 * practicum_claude container has the CLI. Upserts on client + session number,
 * so importing the same PDF again updates the report instead of duplicating it.
 */
class ImportIndividualCounsellingLog implements ShouldQueue
{
    use Queueable;

    public const PROMPT = 'Read report.pdf, an exported counselling session report. '
        .'Reply with only a JSON object, no code fences, with these keys: '
        .'client_code (the code in "Client Name / Code", e.g. A15), session_number (integer), '
        .'session_date (YYYY-MM-DD), start_time (HH:MM, 24-hour), place, '
        .'written_at (YYYY-MM-DD HH:MM, from "Date and Time" at the end), '
        .'part1_subjective_objective, part2_assessment, part3_treatment_plan, part4_evaluation. '
        .'Each part is the text between its "PART n" heading and the next one, as HTML, copied word for word: '
        .'join lines broken across lines or pages, and remove hyphens added only to break a word at a line end. '
        .'Use <p> for paragraphs and <h3> for bold sub-headings (including lettered ones like "A. Treatment Planning"), '
        .'except "Short-Term Goals" and "Long-Term Goals", which are <p><strong>...</strong></p>. '
        .'Use <ul><li><p>...</p></li></ul> for bullet lists, keeping bold lead-ins in <strong>. '
        .'Part 4 stops before "Student / Counselor Trainee Name". Use null for anything missing.';

    /**
     * Columns the transcription may fill; anything else Claude returns is ignored.
     */
    private const FIELDS = [
        'session_date', 'start_time', 'place', 'written_at',
        'part1_subjective_objective', 'part2_assessment', 'part3_treatment_plan', 'part4_evaluation',
    ];

    private const PARTS = [
        'part1_subjective_objective', 'part2_assessment', 'part3_treatment_plan', 'part4_evaluation',
    ];

    /**
     * @param  string  $path  The uploaded PDF on the local disk; deleted once imported.
     */
    public function __construct(public string $path, public Client $client)
    {
        $this->onQueue('summaries');
    }

    public function handle(): void
    {
        // Run outside the project so Claude cannot read the code or .env.
        $directory = sys_get_temp_dir().'/report-import-'.Str::uuid();
        File::ensureDirectoryExists($directory);
        File::copy(Storage::disk('local')->path($this->path), "{$directory}/report.pdf");

        try {
            $result = Process::path($directory)
                ->timeout(80)
                ->run(['claude', '-p', self::PROMPT, '--allowedTools', 'Read'])
                ->throw();
        } finally {
            File::deleteDirectory($directory);
        }

        /** @var array<string, mixed> $report */
        $report = json_decode(trim(preg_replace('/^```(?:json)?|```$/m', '', $result->output())), true, flags: JSON_THROW_ON_ERROR);

        $attributes = Arr::only($report, self::FIELDS);
        $oldCode = trim((string) ($report['client_code'] ?? ''));

        // The report text refers to the client by code, which may have changed since export.
        if ($oldCode !== '' && filled($this->client->client_code) && $oldCode !== $this->client->client_code) {
            foreach (self::PARTS as $part) {
                if (is_string($attributes[$part] ?? null)) {
                    $attributes[$part] = preg_replace('/\b'.preg_quote($oldCode, '/').'\b/', $this->client->client_code, $attributes[$part]);
                }
            }
        }

        $sessionNumber = filter_var($report['session_number'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 9]]);

        IndividualCounsellingLog::updateOrCreate(
            ['client_id' => $this->client->id, 'session_number' => $sessionNumber === false ? null : $sessionNumber],
            $attributes,
        );

        Storage::disk('local')->delete($this->path);
    }
}
