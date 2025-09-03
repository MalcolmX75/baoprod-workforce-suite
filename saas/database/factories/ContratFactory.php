<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BaoProd\Workforce\Models\Contrat>
 */
class ContratFactory extends Factory
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
            'user_id' => 1,
            'job_id' => null,
            'created_by' => 1,
            'numero_contrat' => 'GA-CDI-' . date('Ym') . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'type_contrat' => $this->faker->randomElement(['CDD', 'CDI', 'MISSION', 'STAGE']),
            'statut' => $this->faker->randomElement(['BROUILLON', 'EN_ATTENTE_SIGNATURE', 'SIGNE', 'ACTIF']),
            'date_debut' => $this->faker->dateTimeBetween('now', '+1 month'),
            'date_fin' => $this->faker->optional()->dateTimeBetween('+1 month', '+1 year'),
            'salaire_brut' => $this->faker->numberBetween(50000, 500000),
            'pays_code' => $this->faker->randomElement(['GA', 'CM', 'TD', 'CF', 'GQ', 'CG']),
            'devise' => 'XAF',
            'description' => $this->faker->sentence,
        ];
    }
}
