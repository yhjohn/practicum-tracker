<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\SessionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SessionPlan>
 */
class SessionPlanFactory extends Factory
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
            'session_number' => fake()->unique()->numberBetween(1, 50),
            'plan' => '<p>'.fake()->paragraph().'</p>',
        ];
    }
}
