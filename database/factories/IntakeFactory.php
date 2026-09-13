<?php

namespace Database\Factories;

use App\Enums\IntakeStatus;
use App\Models\Client;
use App\Models\Intake;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Intake>
 */
class IntakeFactory extends Factory
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
            'status' => IntakeStatus::Pending,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => IntakeStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }
}
