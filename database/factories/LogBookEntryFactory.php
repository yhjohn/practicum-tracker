<?php

namespace Database\Factories;

use App\Enums\LogBookActivity;
use App\Models\LogBookEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LogBookEntry>
 */
class LogBookEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'session_date' => fake()->dateTimeBetween('2026-09-13', '2026-12-31'),
            'duration_hours' => fake()->randomElement([1, 1.5, 2, 3]),
            'description' => fake()->sentence(3),
            'activity' => LogBookActivity::ProfessionalDevelopment,
        ];
    }
}
