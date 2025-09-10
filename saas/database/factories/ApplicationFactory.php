<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use BaoProd\Workforce\Models\Application;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Job;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BaoProd\Workforce\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Application::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'job_id' => Job::factory(),
            'candidate_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending', 'reviewed', 'shortlisted', 'interviewed', 'accepted', 'rejected']),
            'cover_letter' => $this->faker->realText(),
            'documents' => null,
            'expected_salary' => $this->faker->optional()->numberBetween(30000, 100000),
            'available_start_date' => $this->faker->optional()->dateTimeBetween('now', '+3 months'),
            'notes' => $this->faker->optional()->sentence,
            'reviewed_at' => $this->faker->optional()->dateTimeThisYear(),
            'reviewed_by' => User::factory(),
        ];
    }
}
