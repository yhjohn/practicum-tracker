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
        Schema::create('report_summaries', function (Blueprint $table) {
            $table->id();
            $table->morphs('summarizable');
            $table->longText('summary');
            $table->timestamps();

            $table->unique(['summarizable_type', 'summarizable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_summaries');
    }
};
