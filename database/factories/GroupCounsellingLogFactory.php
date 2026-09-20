<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupCounsellingLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupCounsellingLog>
 */
class GroupCounsellingLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'session_date' => fake()->date(),
            'session_number' => fake()->numberBetween(1, 9),
            'session_theme' => fake()->words(3, true),
        ];
    }
}
