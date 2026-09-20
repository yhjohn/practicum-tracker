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
        Schema::create('group_counselling_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();

            $table->date('session_date')->nullable();
            $table->time('start_time')->nullable();
            $table->decimal('duration_hours', 4, 2)->nullable();
            $table->string('place')->nullable();
            $table->unsignedTinyInteger('session_number')->nullable();
            $table->unsignedTinyInteger('total_sessions_planned')->nullable();
            $table->string('session_theme')->nullable();
            $table->string('counselling_approach')->nullable();
            $table->string('facilitator_name')->nullable();
            $table->string('supervisor_name')->nullable();

            $table->json('members')->nullable();

            $table->longText('part1_subjective_objective')->nullable();
            $table->longText('part2_assessment')->nullable();
            $table->longText('part3_treatment_plan')->nullable();
            $table->longText('part4_evaluation')->nullable();

            $table->timestamp('written_at')->nullable();
            $table->json('attachments')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_counselling_logs');
    }
};
