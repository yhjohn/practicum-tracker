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
        Schema::create('mental_status_examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('date_of_interview')->nullable();

            $table->json('dress_grooming')->nullable();
            $table->string('dress_grooming_other')->nullable();

            $table->json('motor_function')->nullable();
            $table->json('manual_dexterity')->nullable();
            $table->string('motor_function_other')->nullable();

            $table->json('eye_contact')->nullable();
            $table->string('eye_contact_other')->nullable();

            $table->json('vision')->nullable();
            $table->string('vision_detail')->nullable();

            $table->json('hearing')->nullable();
            $table->string('hearing_detail')->nullable();

            $table->json('verbal_comprehension')->nullable();
            $table->string('verbal_comprehension_detail')->nullable();

            $table->string('orientation_day_of_week')->nullable();
            $table->string('orientation_year')->nullable();
            $table->string('orientation_month')->nullable();
            $table->string('orientation_season')->nullable();
            $table->string('orientation_date')->nullable();
            $table->string('orientation_building_type')->nullable();
            $table->string('orientation_town')->nullable();
            $table->string('orientation_county')->nullable();
            $table->string('orientation_floor')->nullable();
            $table->string('orientation_state')->nullable();
            $table->text('orientation_situation_notes')->nullable();
            $table->string('orientation_situation_rating')->nullable();

            $table->json('speech_fluency')->nullable();
            $table->json('speech_rate')->nullable();
            $table->json('speech_volume')->nullable();
            $table->json('speech_prosody')->nullable();
            $table->string('speech_prosody_other')->nullable();

            $table->json('affect')->nullable();

            $table->json('mood')->nullable();
            $table->string('mood_other')->nullable();

            $table->json('sleep')->nullable();

            $table->json('appetite')->nullable();

            $table->json('thought_process')->nullable();
            $table->string('thought_process_other')->nullable();

            $table->json('thought_content')->nullable();
            $table->string('thought_content_delusions_detail')->nullable();
            $table->string('thought_content_hallucinations_detail')->nullable();
            $table->string('thought_content_other_detail')->nullable();

            $table->json('motivation_effort')->nullable();

            $table->text('strengths')->nullable();
            $table->text('other_observations')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mental_status_examinations');
    }
};
