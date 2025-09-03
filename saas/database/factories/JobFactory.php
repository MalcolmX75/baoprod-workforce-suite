<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BaoProd\Workforce\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => 1,
            'employer_id' => 1,
            'title' => $this->faker->jobTitle,
            'description' => $this->faker->paragraph,
            'requirements' => $this->faker->paragraph,
            'location' => $this->faker->city,
            'salary_min' => $this->faker->numberBetween(50000, 200000),
            'salary_max' => $this->faker->numberBetween(200000, 500000),
            'type' => $this->faker->randomElement(['full_time', 'part_time', 'contract', 'temporary']),
            'status' => 'published',
            'salary_currency' => 'XAF',
            'salary_period' => 'monthly',
        ];
    }
}
