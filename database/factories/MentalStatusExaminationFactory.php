<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\MentalStatusExamination;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MentalStatusExamination>
 */
class MentalStatusExaminationFactory extends Factory
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
            'date_of_interview' => fake()->date(),
            'dress_grooming' => ['Neatly dressed/groomed'],
            'motor_function' => ['Apparently normal gait and upper body movements'],
            'eye_contact' => ['Good'],
            'vision' => ['Intact for appointment purposes'],
            'hearing' => ['Intact for appointment purposes'],
            'verbal_comprehension' => ['Good'],
            'speech_fluency' => ['Normal'],
            'speech_rate' => ['Normal'],
            'speech_volume' => ['Normal'],
            'speech_prosody' => ['Normal'],
            'affect' => ['Normal range'],
            'mood' => ['Normal'],
            'sleep' => ['Good'],
            'appetite' => ['Good'],
            'thought_process' => ['Linear'],
            'thought_content' => ['Normal content'],
            'motivation_effort' => ['Motivated/good effort'],
        ];
    }
}
