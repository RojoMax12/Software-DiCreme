<?php

namespace Database\Factories;

use App\Models\Formato;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Formato>
 */
class FormatoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $formatos = [
            ['nombre_formato' => '10L'],
            ['nombre_formato' => '5L'],
            ['nombre_formato' => '1L'],
        ];

        return $this->faker->randomElement($formatos);
    }
}
