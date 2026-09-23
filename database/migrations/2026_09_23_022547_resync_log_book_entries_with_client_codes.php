<?php

use App\Models\GroupCounsellingLog;
use App\Models\IndividualCounsellingLog;
use App\Models\LogBookEntry;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Re-mirror every counselling log so its log book entry's client_code
     * picks up the client's code instead of the name it was created with.
     */
    public function up(): void
    {
        IndividualCounsellingLog::each(fn (IndividualCounsellingLog $log) => LogBookEntry::syncFrom($log));
        GroupCounsellingLog::each(fn (GroupCounsellingLog $log) => LogBookEntry::syncFrom($log));
    }

    public function down(): void
    {
        // Data-only backfill; nothing to reverse.
    }
};
