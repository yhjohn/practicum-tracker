<?php

namespace Database\Factories;

use App\Models\PdfDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PdfDocument>
 */
class PdfDocumentFactory extends Factory
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
            'files' => ['pdf-documents/'.fake()->slug().'.pdf'],
        ];
    }
}
