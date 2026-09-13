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
            $table->string('type')->after('id');
            $table->nullableMorphs('sessionable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counselling_sessions', function (Blueprint $table) {
            $table->dropMorphs('sessionable');
            $table->dropColumn('type');
        });
    }
};
