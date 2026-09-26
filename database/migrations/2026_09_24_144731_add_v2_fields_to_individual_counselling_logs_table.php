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
        Schema::table('individual_counselling_logs', function (Blueprint $table) {
            $table->string('referral_source')->nullable()->after('place');
            $table->string('counselling_approach')->nullable()->after('referral_source');
            $table->unsignedTinyInteger('total_sessions_planned')->nullable()->after('session_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('individual_counselling_logs', function (Blueprint $table) {
            $table->dropColumn(['referral_source', 'counselling_approach', 'total_sessions_planned']);
        });
    }
};
