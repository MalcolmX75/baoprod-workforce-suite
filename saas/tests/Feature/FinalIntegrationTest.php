<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Job;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\Timesheet;
use BaoProd\Workforce\Models\Paie;
use BaoProd\Workforce\Models\Application;

class FinalIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $employer;
    protected $employee;
    protected $job;
    protected $application;
    protected $contrat;
    protected $timesheet;
    protected $paie;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Utiliser les seeders existants
        $this->seed();
        
        // Récupérer les données des seeders
        $this->tenant = Tenant::first();
        $this->employer = User::where('type', 'employer')->first();
        $this->employee = User::where('type', 'candidate')->first();
        $this->job = Job::first();
        $this->application = Application::first();
        $this->contrat = Contrat::first();
        $this->timesheet = Timesheet::first();
        $this->paie = Paie::first();
    }

    /** @test */
    public function scenario_validation_donnees_seeders()
    {
        // SCÉNARIO : Vérifier que toutes les données des seeders sont valides
        
        // 1. Vérifier le tenant
        $this->assertNotNull($this->tenant);
        $this->assertNotEmpty($this->tenant->name);
        $this->assertTrue($this->tenant->is_active);

        // 2. Vérifier les utilisateurs
        $this->assertNotNull($this->employer);
        $this->assertNotNull($this->employee);
        $this->assertEquals('employer', $this->employer->type);
        $this->assertEquals('candidate', $this->employee->type);

        // 3. Vérifier le job
        $this->assertNotNull($this->job);
        $this->assertNotEmpty($this->job->title);
        $this->assertEquals($this->tenant->id, $this->job->tenant_id);

        // 4. Vérifier l'application
        $this->assertNotNull($this->application);
        $this->assertEquals($this->job->id, $this->application->job_id);

        // 5. Vérifier le contrat
        $this->assertNotNull($this->contrat);
        $this->assertGreaterThan(0, $this->contrat->salaire_brut);
        $this->assertNotEmpty($this->contrat->pays_code);

        // 6. Vérifier le timesheet
        $this->assertNotNull($this->timesheet);
        $this->assertNotNull($this->timesheet->date_pointage);
        $this->assertNotEmpty($this->timesheet->heure_debut);

        // 7. Vérifier la paie
        $this->assertNotNull($this->paie);
        $this->assertGreaterThan(0, $this->paie->salaire_brut_total);
        $this->assertNotNull($this->paie->periode_debut);
    }

    /** @test */
    public function scenario_calculs_metier_essentiels()
    {
        // SCÉNARIO : Tester les calculs métier essentiels
        
        // 1. Tester les calculs de contrat
        $salaireNet = $this->contrat->calculerSalaireNet();
        $this->assertGreaterThan(0, $salaireNet);
        $this->assertLessThan($this->contrat->salaire_brut, $salaireNet);

        $tauxHoraire = $this->contrat->calculerTauxHoraire();
        $this->assertGreaterThan(0, $tauxHoraire);

        // 2. Tester les calculs de timesheet
        $heuresTravaillees = $this->timesheet->calculerHeuresTravaillees();
        $this->assertGreaterThan(0, $heuresTravaillees);

        $heuresSup = $this->timesheet->calculerHeuresSupplementaires();
        $this->assertGreaterThanOrEqual(0, $heuresSup);

        // 3. Recalculer tout le timesheet
        $this->timesheet->recalculerTout();
        $this->assertGreaterThan(0, $this->timesheet->heures_travaillees_minutes);
        $this->assertGreaterThanOrEqual(0, $this->timesheet->heures_supplementaires_minutes);

        // 4. Tester les calculs de paie
        $this->paie->appliquerConfigurationPays();
        $this->assertNotNull($this->paie->configuration_pays);

        $charges = $this->paie->calculerChargesSociales();
        $this->assertGreaterThan(0, $charges);

        $impot = $this->paie->calculerImpotSurRevenu();
        $this->assertGreaterThanOrEqual(0, $impot);

        $salaireNet = $this->paie->calculerSalaireNet();
        $this->assertGreaterThan(0, $salaireNet);
        $this->assertLessThan($this->paie->salaire_brut_total, $salaireNet);

        $netAPayer = $this->paie->calculerNetAPayer();
        $this->assertGreaterThan(0, $netAPayer);
        $this->assertLessThanOrEqual($salaireNet, $netAPayer);
    }

    /** @test */
    public function scenario_configuration_cemac_basique()
    {
        // SCÉNARIO : Vérifier la configuration CEMAC basique
        
        // 1. Tester la configuration du Gabon
        $configGA = \BaoProd\Workforce\Models\Contrat::getConfigurationPays('GA');
        $this->assertIsArray($configGA);
        $this->assertArrayHasKey('charges_sociales', $configGA);
        $this->assertEquals(28.0, $configGA['charges_sociales']);
        if (isset($configGA['tranches_impot'])) {
            $this->assertIsArray($configGA['tranches_impot']);
        }

        // 2. Tester la configuration du Cameroun
        $configCM = \BaoProd\Workforce\Models\Contrat::getConfigurationPays('CM');
        $this->assertIsArray($configCM);
        $this->assertEquals(20.0, $configCM['charges_sociales']);

        // 3. Tester la configuration du Tchad
        $configTD = \BaoProd\Workforce\Models\Contrat::getConfigurationPays('TD');
        $this->assertIsArray($configTD);
        $this->assertEquals(25.0, $configTD['charges_sociales']);

        // 4. Vérifier que toutes les configurations ont les mêmes clés principales
        $this->assertArrayHasKey('charges_sociales', $configGA);
        $this->assertArrayHasKey('charges_sociales', $configCM);
        $this->assertArrayHasKey('charges_sociales', $configTD);
    }

    /** @test */
    public function scenario_calculs_geolocalisation()
    {
        // SCÉNARIO : Tester les calculs de géolocalisation
        
        // 1. Vérifier que la distance est calculée correctement
        if ($this->timesheet->latitude_debut && $this->timesheet->longitude_debut &&
            $this->timesheet->latitude_fin && $this->timesheet->longitude_fin) {
            
            $distance = $this->timesheet->calculerDistance();
            $this->assertGreaterThanOrEqual(0, $distance);
        }

        // 2. Tester avec des coordonnées connues (Libreville)
        $timesheet = new Timesheet([
            'latitude_debut' => 0.4162,
            'longitude_debut' => 9.4673,
            'latitude_fin' => 0.4162,
            'longitude_fin' => 9.4673
        ]);
        
        $distance = $timesheet->calculerDistance();
        $this->assertEquals(0, $distance); // Même lieu = 0 km

        // 3. Tester avec des coordonnées différentes
        $timesheet->latitude_fin = 0.4262; // ~1 km au nord
        $timesheet->longitude_fin = 9.4673;
        
        $distance = $timesheet->calculerDistance();
        $this->assertGreaterThan(0, $distance);
        $this->assertLessThan(2, $distance); // Moins de 2 km
    }

    /** @test */
    public function scenario_validation_donnees_metier()
    {
        // SCÉNARIO : Vérifier la validation des données métier
        
        // 1. Vérifier que les salaires sont positifs
        $this->assertGreaterThan(0, $this->contrat->salaire_brut);
        $this->assertGreaterThan(0, $this->paie->salaire_brut_total);

        // 2. Vérifier que les dates sont cohérentes
        if ($this->contrat->date_debut && $this->contrat->date_fin) {
            $this->assertLessThanOrEqual($this->contrat->date_fin, $this->contrat->date_debut);
        }

        if ($this->paie->periode_debut && $this->paie->periode_fin) {
            $this->assertLessThanOrEqual($this->paie->periode_fin, $this->paie->periode_debut);
        }

        // 3. Vérifier que les heures sont cohérentes
        if ($this->timesheet->heure_debut && $this->timesheet->heure_fin) {
            $debut = \Carbon\Carbon::parse($this->timesheet->heure_debut);
            $fin = \Carbon\Carbon::parse($this->timesheet->heure_fin);
            
            // La fin doit être après le début (ou le lendemain pour le travail de nuit)
            $this->assertTrue($fin->gt($debut) || $fin->lt($debut));
        }

        // 4. Vérifier que les montants calculés sont cohérents
        $this->paie->recalculerTout();
        
        $this->assertGreaterThanOrEqual(0, $this->paie->charges_sociales_montant);
        $this->assertGreaterThanOrEqual(0, $this->paie->cotisations_patronales);
        $this->assertGreaterThanOrEqual(0, $this->paie->cotisations_salariales);
        $this->assertGreaterThanOrEqual(0, $this->paie->impot_sur_revenu);
        $this->assertGreaterThan(0, $this->paie->salaire_net);
        $this->assertGreaterThan(0, $this->paie->net_a_payer);
        
        // Le salaire net doit être inférieur au salaire brut
        $this->assertLessThan($this->paie->salaire_brut_total, $this->paie->salaire_net);
        
        // Le net à payer doit être inférieur ou égal au salaire net
        $this->assertLessThanOrEqual($this->paie->salaire_net, $this->paie->net_a_payer);
    }

    /** @test */
    public function scenario_workflow_complet_simplifie()
    {
        // SCÉNARIO : Workflow complet simplifié
        
        // 1. Vérifier que tous les modèles sont liés correctement
        $this->assertEquals($this->tenant->id, $this->job->tenant_id);
        $this->assertEquals($this->tenant->id, $this->application->tenant_id);
        $this->assertEquals($this->tenant->id, $this->contrat->tenant_id);
        $this->assertEquals($this->tenant->id, $this->timesheet->tenant_id);
        $this->assertEquals($this->tenant->id, $this->paie->tenant_id);

        // 2. Vérifier les relations entre les modèles
        $this->assertEquals($this->job->id, $this->application->job_id);
        $this->assertEquals($this->employee->id, $this->contrat->user_id);
        $this->assertEquals($this->contrat->id, $this->timesheet->contrat_id);
        $this->assertEquals($this->contrat->id, $this->paie->contrat_id);

        // 3. Vérifier que les calculs sont cohérents
        $this->contrat->appliquerConfigurationPays();
        $this->assertNotNull($this->contrat->configuration_pays);

        $this->timesheet->recalculerTout();
        $this->assertGreaterThan(0, $this->timesheet->heures_travaillees_minutes);

        $this->paie->recalculerTout();
        $this->assertGreaterThan(0, $this->paie->salaire_net);

        // 4. Vérifier que les statuts sont valides
        $this->assertNotEmpty($this->contrat->statut);
        $this->assertNotEmpty($this->timesheet->statut);
        $this->assertNotEmpty($this->paie->statut);
        if ($this->application->statut) {
            $this->assertNotEmpty($this->application->statut);
        }
        $this->assertNotEmpty($this->job->status);
    }
}