<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\BaoProd\Workforce\Models\Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'domain' => $this->faker->domainName,
            'subdomain' => $this->faker->slug,
            'country_code' => $this->faker->randomElement(['GA', 'CM', 'TD', 'CF', 'GQ', 'CG']),
            'currency' => 'XAF',
            'language' => 'fr',
            'settings' => [],
            'modules' => ['contrats'],
            'is_active' => true,
        ];
    }
}
