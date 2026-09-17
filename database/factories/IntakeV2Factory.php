<?php

namespace Database\Factories;

use App\Enums\IntakeStatus;
use App\Models\Client;
use App\Models\IntakeV2;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<IntakeV2>
 */
class IntakeV2Factory extends Factory
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

    public function withAccessCode(string $code): static
    {
        return $this->state(fn (array $attributes): array => [
            'access_code' => Hash::make($code),
        ]);
    }
}
