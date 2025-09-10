<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use BaoProd\Workforce\Models\Timesheet;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Contrat;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BaoProd\Workforce\Models\Timesheet>
 */
class TimesheetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Timesheet::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'contrat_id' => Contrat::factory(),
            'job_id' => null,
            'date_pointage' => $this->faker->dateTimeThisMonth(),
            'heure_debut' => '08:00',
            'heure_fin' => '17:00',
            'statut' => $this->faker->randomElement(['EN_ATTENTE_VALIDATION', 'VALIDE', 'REJETE', 'BROUILLON']),
            'pays_code' => 'GA',
            'devise' => 'XAF',
        ];
    }
}
