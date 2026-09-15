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
        Schema::create('intakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');

            // Personal information
            $table->string('name')->nullable();
            $table->date('dob')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->string('sex')->nullable();
            $table->string('emergency_name')->nullable();
            $table->string('emergency_relationship')->nullable();
            $table->string('emergency_phone')->nullable();

            // Marital / relationship status
            $table->json('marital')->nullable();
            $table->string('relationship_length')->nullable();
            $table->unsignedSmallInteger('marriages')->nullable();

            // Education
            $table->string('education')->nullable();
            $table->json('education_current')->nullable();
            $table->text('education_details')->nullable();

            // Career / employment
            $table->string('job_title')->nullable();
            $table->text('employment_history')->nullable();
            $table->text('job_satisfaction')->nullable();

            // Personal strengths and supports
            $table->text('strength_activities')->nullable();
            $table->text('strengths')->nullable();
            $table->text('others_strengths')->nullable();
            $table->text('supports')->nullable();

            // Traumatic experiences or significant losses
            $table->json('trauma')->nullable();
            $table->text('trauma_details')->nullable();

            // Family history
            $table->text('family_description')->nullable();
            $table->text('childhood_experience')->nullable();
            $table->text('family_support')->nullable();

            // Counselling / medical history
            $table->text('counselling_history')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('therapy_experience')->nullable();
            $table->text('medical_history')->nullable();

            // Substance use
            $table->text('substance_use')->nullable();
            $table->text('substance_treatment')->nullable();

            // Legal issues
            $table->text('legal_issues')->nullable();
            $table->text('legal_impact')->nullable();

            // Emotional / mental wellbeing
            $table->text('emotions')->nullable();
            $table->text('safety')->nullable();
            $table->text('support_system')->nullable();
            $table->text('current_stress')->nullable();
            $table->text('memory')->nullable();

            // Spirituality / beliefs
            $table->text('belief_system')->nullable();
            $table->text('spiritual_upbringing')->nullable();

            // Reasons for seeking counselling
            $table->text('seeking_reason')->nullable();
            $table->text('first_occurred')->nullable();
            $table->text('main_concern')->nullable();
            $table->text('family_concern')->nullable();
            $table->text('counselling_outcome')->nullable();
            $table->text('anything_else')->nullable();

            // Confidentiality acknowledgement and signature
            $table->longText('signature')->nullable();
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
        Schema::dropIfExists('intakes');
    }
};
