<?php

namespace Database\Factories;

use App\Models\Deputy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deputy>
 */
class DeputyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'camara_id' => fake()->unique()->numberBetween(100000, 999999),
            'name' => fake()->name(),
            'party_acronym' => fake()->randomElement(['MDB', 'PL', 'PSB', 'PT', 'UNIÃO']),
            'state_acronym' => fake()->randomElement(['BA', 'DF', 'MG', 'PR', 'RJ', 'RS', 'SP']),
            'legislature_id' => 57,
            'email' => fake()->unique()->safeEmail(),
            'photo_url' => fake()->imageUrl(),
            'api_url' => fake()->url(),
            'party_api_url' => fake()->url(),
        ];
    }
}
