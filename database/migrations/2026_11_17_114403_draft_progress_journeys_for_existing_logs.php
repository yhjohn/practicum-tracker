<?php

use App\Jobs\DraftProgressJourney;
use App\Models\IndividualCounsellingLog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Queue a Claude-drafted progress journey for every existing log that has
     * no entry yet (the job skips empty logs); the summaries queue works through them.
     */
    public function up(): void
    {
        IndividualCounsellingLog::doesntHave('progressJourney')
            ->each(fn (IndividualCounsellingLog $log) => DraftProgressJourney::dispatch($log));
    }

    public function down(): void
    {
        // Data-only backfill; nothing to reverse.
    }
};
