<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\IndividualCounsellingLog;
use App\Models\ProgressJourney;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProgressJourney>
 */
class ProgressJourneyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'individual_counselling_log_id' => fn (array $attributes): int => IndividualCounsellingLog::create([
                'client_id' => $attributes['client_id'],
                'session_number' => IndividualCounsellingLog::where('client_id', $attributes['client_id'])->max('session_number') + 1,
                'session_date' => fake()->date(),
                'start_time' => '10:00',
            ])->id,
            'background' => '<p>'.fake()->paragraph().'</p>',
            'case_conceptualisation' => '<p>'.fake()->paragraph().'</p>',
            'interventions' => '<p>'.fake()->paragraph().'</p>',
            'progress_summary' => '<p>'.fake()->paragraph().'</p>',
        ];
    }
}
