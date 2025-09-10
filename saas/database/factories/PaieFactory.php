<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use BaoProd\Workforce\Models\Paie;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Contrat;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BaoProd\Workforce\Models\Paie>
 */
class PaieFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Paie::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeThisMonth()->format('Y-m-d');
        $endDate = Carbon::parse($startDate)->endOfMonth()->format('Y-m-d');

        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'contrat_id' => Contrat::factory(),
            'periode_debut' => $startDate,
            'periode_fin' => $endDate,
            'numero_bulletin' => 'BULLETIN-' . $this->faker->unique()->numerify('#####'),
            'statut' => $this->faker->randomElement(['BROUILLON', 'GENERE', 'PAYE', 'ANNULE']),
            'salaire_brut_total' => $this->faker->numberBetween(150000, 600000),
            'pays_code' => 'GA',
            'devise' => 'XAF',
        ];
    }
}
