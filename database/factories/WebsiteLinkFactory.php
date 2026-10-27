<?php

namespace Database\Factories;

use App\Models\WebsiteLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebsiteLink>
 */
class WebsiteLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'url' => fake()->url(),
        ];
    }
}
