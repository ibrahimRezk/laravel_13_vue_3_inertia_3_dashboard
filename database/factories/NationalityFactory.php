<?php

namespace Database\Factories;

use App\Models\Nationality;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nationality>
 */
class NationalityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => [
                'en' => fake('english')->name(),
                'ar' => fake('arabic')->name(),
            ],
            'active' => fake()->boolean(),
            'date' => now()->toDateString(),
            // Resolved to a real user id by the factory; kept out of the seeder's
            // hard-coded id 1 so tests can create nationalities in isolation.
            'added_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the nationality is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => ['active' => true]);
    }

    /**
     * Indicate that the nationality is inactive.
     */
    public function inActive(): static
    {
        return $this->state(fn (array $attributes) => ['active' => false]);
    }

    /**
     * Indicate that the nationality has already been used on the system.
     */
    public function usedBefore(): static
    {
        return $this->state(fn (array $attributes) => ['used_before' => true]);
    }
}
