<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Models\Job;
use BaoProd\Workforce\Services\ContratTemplateService;

class ContratTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $tenant;
    protected $user;
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
            'modules' => ['core', 'contrats'],
            'is_active' => true,
        ]);

        // Créer un utilisateur de test
        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'type' => 'employer',
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
    }

    /** @test */
    public function it_can_create_a_contrat()
    {
        $contratData = [
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'type_contrat' => 'CDD',
            'date_debut' => now()->addDays(7),
            'date_fin' => now()->addMonths(6),
            'salaire_brut' => 120000,
            'pays_code' => 'GA',
            'description' => 'Test contrat',
        ];

        $contrat = new Contrat();
        $contrat->fill($contratData);
        $contrat->tenant_id = $this->tenant->id;
        $contrat->created_by = $this->user->id;
        
        // Appliquer la configuration du pays
        $contrat->appliquerConfigurationPays();
        
        // Calculer les valeurs dérivées
        $contrat->salaire_net = $contrat->calculerSalaireNet();
        $contrat->taux_horaire = $contrat->calculerTauxHoraire();
        $contrat->charges_sociales_montant = $contrat->salaire_brut * ($contrat->charges_sociales_pourcentage / 100);
        
        // Générer le numéro de contrat
        $contrat->numero_contrat = 'GA-CDD-' . date('Ym') . '-0001';
        
        $contrat->save();

        $this->assertDatabaseHas('contrats', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type_contrat' => 'CDD',
            'pays_code' => 'GA',
        ]);

        $this->assertEquals(120000, $contrat->salaire_brut);
        $this->assertEquals(28.0, $contrat->charges_sociales_pourcentage);
        $this->assertEquals(40, $contrat->heures_semaine);
    }

    /** @test */
    public function it_can_calculate_salaire_net()
    {
        $contrat = new Contrat([
            'salaire_brut' => 100000,
            'charges_sociales_pourcentage' => 28.0,
        ]);

        $salaireNet = $contrat->calculerSalaireNet();
        
        $this->assertEquals(72000, $salaireNet); // 100000 - (100000 * 0.28)
    }

    /** @test */
    public function it_can_calculate_taux_horaire()
    {
        $contrat = new Contrat([
            'salaire_brut' => 120000,
            'heures_mois' => 173,
        ]);

        $tauxHoraire = $contrat->calculerTauxHoraire();
        
        $this->assertEquals(693.64, round($tauxHoraire, 2)); // 120000 / 173
    }

    /** @test */
    public function it_can_apply_country_configuration()
    {
        $contrat = new Contrat([
            'pays_code' => 'GA',
        ]);

        $contrat->appliquerConfigurationPays();

        $this->assertEquals(40, $contrat->heures_semaine);
        $this->assertEquals(28.0, $contrat->charges_sociales_pourcentage);
        $this->assertEquals(80000, $contrat->smig);
    }

    /** @test */
    public function it_can_generate_contrat_html()
    {
        $contrat = Contrat::create([
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
            'statut' => 'BROUILLON',
        ]);

        $templateService = new ContratTemplateService();
        $html = $templateService->generateContratHtml($contrat);

        $this->assertStringContainsString('CONTRAT DE TRAVAIL', $html);
        $this->assertStringContainsString('République Gabonaise', $html);
        $this->assertStringContainsString($contrat->numero_contrat, $html);
        $this->assertStringContainsString($this->user->full_name, $html);
    }

    /** @test */
    public function it_can_get_country_configuration()
    {
        $configGA = Contrat::getConfigurationPays('GA');
        $configCM = Contrat::getConfigurationPays('CM');

        $this->assertEquals(40, $configGA['heures_semaine']);
        $this->assertEquals(28.0, $configGA['charges_sociales']);
        $this->assertEquals(80000, $configGA['smig']);

        $this->assertEquals(40, $configCM['heures_semaine']);
        $this->assertEquals(20.0, $configCM['charges_sociales']);
        $this->assertEquals(36270, $configCM['smig']);
    }

    /** @test */
    public function it_can_check_contrat_status()
    {
        $contrat = new Contrat(['statut' => 'ACTIF']);
        $this->assertTrue($contrat->is_actif);

        $contrat = new Contrat(['statut' => 'SIGNE']);
        $this->assertTrue($contrat->is_signe);

        $contrat = new Contrat(['statut' => 'BROUILLON']);
        $this->assertTrue($contrat->peutEtreModifie());

        $contrat = new Contrat(['statut' => 'EN_ATTENTE_SIGNATURE']);
        $this->assertTrue($contrat->peutEtreSigne());
    }
}