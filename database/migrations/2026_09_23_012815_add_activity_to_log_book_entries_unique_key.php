<?php

use App\Models\GroupCounsellingLog;
use App\Models\IndividualCounsellingLog;
use App\Models\LogBookEntry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('log_book_entries', function (Blueprint $table) {
            $table->unique(['loggable_type', 'loggable_id', 'activity']);
            $table->dropUnique(['loggable_type', 'loggable_id']);
        });

        IndividualCounsellingLog::each(fn (IndividualCounsellingLog $log) => LogBookEntry::syncFrom($log));
        GroupCounsellingLog::each(fn (GroupCounsellingLog $log) => LogBookEntry::syncFrom($log));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        LogBookEntry::whereNotNull('loggable_id')
            ->where('activity', '!=', 'individual_counselling')
            ->where('activity', '!=', 'group_counselling')
            ->delete();

        Schema::table('log_book_entries', function (Blueprint $table) {
            $table->unique(['loggable_type', 'loggable_id']);
            $table->dropUnique(['loggable_type', 'loggable_id', 'activity']);
        });
    }
};
