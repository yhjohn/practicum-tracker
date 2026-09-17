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
        Schema::create('intake_v2s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('access_code')->nullable();

            // Section 1: Personal information (current model, minus dob)
            $table->string('name')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->string('sex')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_phone')->nullable();

            // Section 1: Marital / relationship status
            $table->string('marital')->nullable();

            // Section 2: Education, Career & Personal Strengths
            $table->string('education_level')->nullable();
            $table->string('education_level_other')->nullable();
            $table->string('job_satisfaction')->nullable();
            $table->json('enjoyable_activities')->nullable();
            $table->string('enjoyable_activities_other')->nullable();
            $table->json('personal_strengths')->nullable();
            $table->string('personal_strengths_other')->nullable();
            $table->json('coping_resources')->nullable();
            $table->string('coping_resources_other')->nullable();
            $table->text('strengths_to_build')->nullable();

            // Section 3: Family, Social & Personal Background
            $table->json('family_relationships')->nullable();
            $table->string('family_relationships_other')->nullable();
            $table->json('childhood_experiences')->nullable();
            $table->string('childhood_experiences_other')->nullable();
            $table->string('family_support_received')->nullable();
            $table->json('beliefs')->nullable();
            $table->string('beliefs_other')->nullable();
            $table->string('spiritual_upbringing_role')->nullable();
            $table->json('substances_used')->nullable();
            $table->string('substances_used_other')->nullable();
            $table->string('substance_use_frequency')->nullable();
            $table->string('substance_use_frequency_other')->nullable();
            $table->text('background_notes')->nullable();

            // Section 4: Mental Health, Current Concerns & Counselling Goals
            $table->string('previous_counselling')->nullable();
            $table->string('previous_counselling_when')->nullable();
            $table->string('mental_health_diagnosis')->nullable();
            $table->text('mental_health_diagnosis_details')->nullable();
            $table->json('psychiatric_services')->nullable();
            $table->text('previous_support_feedback')->nullable();
            $table->json('mood_recent')->nullable();
            $table->string('mood_recent_other')->nullable();
            $table->string('harm_thoughts')->nullable();
            $table->text('harm_thoughts_response')->nullable();
            $table->json('current_stressors')->nullable();
            $table->string('current_stressors_other')->nullable();
            $table->string('current_stressors_impact')->nullable();
            $table->text('main_concern')->nullable();
            $table->json('counselling_goals')->nullable();
            $table->string('counselling_goals_other')->nullable();
            $table->text('anything_else')->nullable();

            $table->date('signature_date')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intake_v2s');
    }
};
