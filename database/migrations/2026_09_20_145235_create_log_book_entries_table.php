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
        Schema::create('log_book_entries', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('loggable');

            $table->date('session_date');
            $table->time('start_time')->nullable();
            $table->decimal('duration_hours', 4, 2)->default(0);
            $table->string('description');
            $table->string('activity');
            $table->string('client_code')->nullable();
            $table->unsignedSmallInteger('number_of_clients')->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['loggable_type', 'loggable_id']);
        });

        IndividualCounsellingLog::each(fn (IndividualCounsellingLog $log) => LogBookEntry::syncFrom($log));
        GroupCounsellingLog::each(fn (GroupCounsellingLog $log) => LogBookEntry::syncFrom($log));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_book_entries');
    }
};
