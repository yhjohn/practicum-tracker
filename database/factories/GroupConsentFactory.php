<?php

namespace Database\Factories;

use App\Enums\ConsentStatus;
use App\Models\Client;
use App\Models\Group;
use App\Models\GroupConsent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupConsent>
 */
class GroupConsentFactory extends Factory
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
            'group_id' => Group::factory(),
            'status' => ConsentStatus::Pending,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ConsentStatus::Submitted,
            'full_name' => $this->faker->name(),
            'dob' => $this->faker->date(),
            'agree' => true,
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            'signature_date' => now()->toDateString(),
            'submitted_at' => now(),
        ]);
    }
}
