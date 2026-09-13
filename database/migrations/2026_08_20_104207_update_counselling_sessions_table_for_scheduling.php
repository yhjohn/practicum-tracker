<?php

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
        Schema::table('counselling_sessions', function (Blueprint $table) {
            $table->dropColumn('session_date');
        });

        Schema::table('counselling_sessions', function (Blueprint $table) {
            $table->date('session_date')->after('type');
            $table->time('start_time')->nullable()->after('session_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->unsignedInteger('duration_minutes')->nullable()->after('end_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counselling_sessions', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time', 'duration_minutes', 'session_date']);
        });

        Schema::table('counselling_sessions', function (Blueprint $table) {
            $table->dateTime('session_date')->after('type');
        });
    }
};
