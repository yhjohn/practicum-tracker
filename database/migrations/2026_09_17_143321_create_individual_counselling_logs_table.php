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
        Schema::create('individual_counselling_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->date('session_date')->nullable();
            $table->time('start_time')->nullable();
            $table->decimal('duration_hours', 4, 2)->nullable();
            $table->string('place')->nullable();
            $table->unsignedTinyInteger('session_number')->nullable();

            $table->longText('part1_subjective_objective')->nullable();
            $table->longText('part2_assessment')->nullable();
            $table->longText('part3_treatment_plan')->nullable();
            $table->longText('part4_evaluation')->nullable();

            $table->longText('signature')->nullable();
            $table->timestamp('signed_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('individual_counselling_logs');
    }
};
