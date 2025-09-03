<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use BaoProd\Workforce\Models\Paie;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\Job;
use Carbon\Carbon;

class PaieTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenant;
    protected $user;
    protected $contrat;
    protected $job;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer un tenant de test
        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'domain' => 'test.local',
            'subdomain' => 'test',
            'country_code' => 'GA',
            'currency' => 'XOF',
            'language' => 'fr',
            'modules' => ['core', 'paie'],
            'is_active' => true,
        ]);

        // Créer un utilisateur de test
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'type' => 'candidate',
            'is_active' => true,
        ]);

        // Créer un job de test
        $this->job = Job::create([
            'tenant_id' => $this->tenant->id,
            'employer_id' => $this->user->id,
            'title' => 'Test Job',
            'description' => 'Test Description',
            'location' => 'Test Location',
            'type' => 'full_time',
            'salary_min' => 100000,
            'salary_max' => 150000,
            'salary_currency' => 'XOF',
            'salary_period' => 'monthly',
            'positions_available' => 1,
            'experience_required' => 1,
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Créer un contrat de test
        $this->contrat = Contrat::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'created_by' => $this->user->id,
            'numero_contrat' => 'GA-CDD-' . date('Ym') . '-0001',
            'type_contrat' => 'CDD',
            'date_debut' => now()->addDays(7),
            'date_fin' => now()->addMonths(6),
            'salaire_brut' => 120000,
            'pays_code' => 'GA',
            'description' => 'Test contrat',
            'statut' => 'ACTIF',
        ]);
    }

    /** @test */
    public function it_can_create_a_paie()
    {
        $paieData = [
            'user_id' => $this->user->id,
            'contrat_id' => $this->contrat->id,
            'periode_debut' => now()->startOfMonth()->format('Y-m-d'),
            'periode_fin' => now()->endOfMonth()->format('Y-m-d'),
            'pays_code' => 'GA',
            'description' => 'Test bulletin de paie',
        ];

        $paie = new Paie();
        $paie->fill($paieData);
        $paie->tenant_id = $this->tenant->id;
        $paie->modifie_par = $this->user->id;
        $paie->derniere_modification = now();
        
        // Appliquer la configuration du pays
        $paie->appliquerConfigurationPays();
        
        // Générer le numéro de bulletin
        $paie->numero_bulletin = $paie->genererNumeroBulletin();
        
        $paie->statut = 'BROUILLON';
        $paie->save();

        $this->assertDatabaseHas('paie', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'contrat_id' => $this->contrat->id,
            'pays_code' => 'GA',
        ]);

        $this->assertEquals(28.0, $paie->charges_sociales_pourcentage);
        $this->assertEquals(80000, $paie->smig);
    }

    /** @test */
    public function it_can_calculate_salaire_brut()
    {
        $paie = new Paie([
            'montant_heures_normales' => 80000,
            'montant_heures_sup' => 10000,
            'montant_heures_nuit' => 5000,
            'indemnites' => 3000,
            'primes' => 2000,
        ]);

        $salaireBrut = $paie->calculerSalaireBrut();
        
        $this->assertEquals(100000, $salaireBrut); // 80000 + 10000 + 5000 + 3000 + 2000
    }

    /** @test */
    public function it_can_calculate_charges_sociales()
    {
        $paie = new Paie([
            'salaire_brut_total' => 100000,
            'pays_code' => 'GA',
        ]);

        $paie->appliquerConfigurationPays();
        $charges = $paie->calculerChargesSociales();
        
        $this->assertEquals(28000, $charges); // 100000 * 0.28
        $this->assertEquals(21500, $paie->cotisations_patronales); // 100000 * 0.215
        $this->assertEquals(6500, $paie->cotisations_salariales); // 100000 * 0.065
    }

    /** @test */
    public function it_can_calculate_impot_sur_revenu()
    {
        $paie = new Paie([
            'salaire_brut_total' => 150000,
            'cotisations_salariales' => 9750, // 6.5% de 150000
            'pays_code' => 'GA',
        ]);

        $paie->appliquerConfigurationPays();
        $impot = $paie->calculerImpotSurRevenu();
        
        // Salaire imposable = 150000 - 9750 = 140250
        // Tranche 1: 0-50000 = 0
        // Tranche 2: 50000-100000 = 50000 * 5% = 2500
        // Tranche 3: 100000-140250 = 40250 * 10% = 4025
        // Total = 2500 + 4025 = 6525
        $this->assertEquals(6525, $impot);
    }

    /** @test */
    public function it_can_calculate_salaire_net()
    {
        $paie = new Paie([
            'salaire_brut_total' => 100000,
            'cotisations_salariales' => 6500,
            'impot_sur_revenu' => 2500,
            'autres_retenues' => 1000,
        ]);

        $salaireNet = $paie->calculerSalaireNet();
        
        $this->assertEquals(90000, $salaireNet); // 100000 - 6500 - 2500 - 1000
    }

    /** @test */
    public function it_can_calculate_net_a_payer()
    {
        $paie = new Paie([
            'salaire_net' => 90000,
            'avances' => 10000,
        ]);

        $netAPayer = $paie->calculerNetAPayer();
        
        $this->assertEquals(80000, $netAPayer); // 90000 - 10000
    }

    /** @test */
    public function it_can_recalculate_everything()
    {
        $paie = new Paie([
            'montant_heures_normales' => 80000,
            'montant_heures_sup' => 10000,
            'montant_heures_nuit' => 5000,
            'indemnites' => 3000,
            'primes' => 2000,
            'avances' => 5000,
            'autres_retenues' => 1000,
            'pays_code' => 'GA',
        ]);

        $paie->appliquerConfigurationPays();
        $paie->recalculerTout();

        $this->assertEquals(100000, $paie->salaire_brut_total); // Salaire brut
        $this->assertEquals(28000, $paie->charges_sociales_montant); // Charges sociales
        $this->assertGreaterThan(0, $paie->impot_sur_revenu); // Impôt
        $this->assertGreaterThan(0, $paie->salaire_net); // Salaire net
        $this->assertGreaterThan(0, $paie->net_a_payer); // Net à payer
    }

    /** @test */
    public function it_can_generate_bulletin_number()
    {
        $paie = new Paie([
            'pays_code' => 'GA',
            'periode_debut' => now()->startOfMonth(),
        ]);

        $numero = $paie->genererNumeroBulletin();
        
        $this->assertStringStartsWith('GA-BULLETIN-', $numero);
        $this->assertStringContainsString(now()->format('Ym'), $numero);
    }

    /** @test */
    public function it_can_apply_country_configuration()
    {
        $paie = new Paie([
            'pays_code' => 'GA',
        ]);

        $paie->appliquerConfigurationPays();

        $this->assertEquals(28.0, $paie->charges_sociales_pourcentage);
        $this->assertEquals(80000, $paie->smig);
        $this->assertArrayHasKey('charges_sociales', $paie->configuration_pays);
        $this->assertArrayHasKey('tranches_impot', $paie->configuration_pays);
    }

    /** @test */
    public function it_can_check_paie_status()
    {
        $paie = new Paie(['statut' => 'PAYE']);
        $this->assertTrue($paie->is_paye);

        $paie = new Paie(['statut' => 'GENERE']);
        $this->assertTrue($paie->is_genere);

        $paie = new Paie(['statut' => 'BROUILLON']);
        $this->assertTrue($paie->peutEtreModifie());

        $paie = new Paie(['statut' => 'GENERE']);
        $this->assertTrue($paie->peutEtrePaye());
        $this->assertTrue($paie->peutEtreAnnule());
    }

    /** @test */
    public function it_can_get_country_configuration()
    {
        $configGA = Paie::getConfigurationPays('GA');
        $configCM = Paie::getConfigurationPays('CM');

        $this->assertEquals(28.0, $configGA['charges_sociales']);
        $this->assertEquals(21.5, $configGA['charges_employeur']);
        $this->assertEquals(6.5, $configGA['charges_salarie']);
        $this->assertEquals(80000, $configGA['smig']);

        $this->assertEquals(20.0, $configCM['charges_sociales']);
        $this->assertEquals(15.5, $configCM['charges_employeur']);
        $this->assertEquals(4.5, $configCM['charges_salarie']);
        $this->assertEquals(36270, $configCM['smig']);
    }

    /** @test */
    public function it_can_calculate_period_duration()
    {
        $paie = new Paie([
            'periode_debut' => Carbon::parse('2025-01-01'),
            'periode_fin' => Carbon::parse('2025-01-31'),
        ]);

        $this->assertEquals(31, $paie->duree_periode);
        $this->assertEquals('01/01/2025 - 31/01/2025', $paie->periode_formatee);
    }
}