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
        Schema::create('progress_journeys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('individual_counselling_log_id')->unique()->constrained()->cascadeOnDelete();
            $table->longText('background')->nullable();
            $table->longText('case_conceptualisation')->nullable();
            $table->longText('interventions')->nullable();
            $table->longText('progress_summary')->nullable();
            $table->timestamp('ai_drafted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_journeys');
    }
};
