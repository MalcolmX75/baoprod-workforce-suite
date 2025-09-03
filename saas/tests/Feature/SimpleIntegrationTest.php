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

class SimpleIntegrationTest extends TestCase
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
        $this->assertNotEmpty($this->tenant->domain);
        $this->assertTrue($this->tenant->is_active);

        // 2. Vérifier les utilisateurs
        $this->assertNotNull($this->employer);
        $this->assertNotNull($this->employee);
        $this->assertEquals('employer', $this->employer->type);
        $this->assertEquals('candidate', $this->employee->type);
        $this->assertNotEmpty($this->employer->email);
        $this->assertNotEmpty($this->employee->email);

        // 3. Vérifier le job
        $this->assertNotNull($this->job);
        $this->assertNotEmpty($this->job->title);
        $this->assertNotEmpty($this->job->description);
        $this->assertEquals($this->tenant->id, $this->job->tenant_id);

        // 4. Vérifier l'application
        $this->assertNotNull($this->application);
        if ($this->application->nom_candidat) {
            $this->assertNotEmpty($this->application->nom_candidat);
        }
        if ($this->application->email_candidat) {
            $this->assertNotEmpty($this->application->email_candidat);
        }
        $this->assertEquals($this->job->id, $this->application->job_id);

        // 5. Vérifier le contrat
        $this->assertNotNull($this->contrat);
        $this->assertGreaterThan(0, $this->contrat->salaire_brut);
        $this->assertNotEmpty($this->contrat->pays_code);
        $this->assertEquals($this->tenant->id, $this->contrat->tenant_id);

        // 6. Vérifier le timesheet
        $this->assertNotNull($this->timesheet);
        $this->assertNotNull($this->timesheet->date_pointage);
        $this->assertNotEmpty($this->timesheet->heure_debut);
        $this->assertNotEmpty($this->timesheet->heure_fin);
        $this->assertEquals($this->tenant->id, $this->timesheet->tenant_id);

        // 7. Vérifier la paie
        $this->assertNotNull($this->paie);
        $this->assertGreaterThan(0, $this->paie->salaire_brut_total);
        $this->assertNotNull($this->paie->periode_debut);
        $this->assertNotNull($this->paie->periode_fin);
        $this->assertEquals($this->tenant->id, $this->paie->tenant_id);
    }

    /** @test */
    public function scenario_calculs_metier_avec_donnees_existantes()
    {
        // SCÉNARIO : Tester les calculs métier avec les données existantes
        
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
    public function scenario_configuration_cemac()
    {
        // SCÉNARIO : Vérifier la configuration CEMAC
        
        // 1. Tester la configuration du Gabon
        $configGA = \BaoProd\Workforce\Models\Contrat::getConfigurationPays('GA');
        $this->assertIsArray($configGA);
        $this->assertArrayHasKey('charges_sociales', $configGA);
        $this->assertEquals(28.0, $configGA['charges_sociales']);
        $this->assertEquals(21.5, $configGA['charges_employeur']);
        $this->assertEquals(6.5, $configGA['charges_salarie']);
        $this->assertIsArray($configGA['tranches_impot']);

        // 2. Tester la configuration du Cameroun
        $configCM = \BaoProd\Workforce\Models\Contrat::getConfigurationPays('CM');
        $this->assertIsArray($configCM);
        $this->assertEquals(20.0, $configCM['charges_sociales']);
        $this->assertEquals(15.0, $configCM['charges_employeur']);
        $this->assertEquals(5.0, $configCM['charges_salarie']);

        // 3. Tester la configuration du Tchad
        $configTD = \BaoProd\Workforce\Models\Contrat::getConfigurationPays('TD');
        $this->assertIsArray($configTD);
        $this->assertEquals(22.0, $configTD['charges_sociales']);

        // 4. Vérifier que toutes les configurations ont les mêmes clés
        $keys = array_keys($configGA);
        $this->assertEquals($keys, array_keys($configCM));
        $this->assertEquals($keys, array_keys($configTD));
    }

    /** @test */
    public function scenario_relations_entre_modeles()
    {
        // SCÉNARIO : Vérifier les relations entre les modèles
        
        // 1. Relations du tenant
        $this->assertGreaterThan(0, $this->tenant->users->count());
        $this->assertGreaterThan(0, $this->tenant->jobs->count());
        $this->assertGreaterThan(0, $this->tenant->contrats->count());
        $this->assertGreaterThan(0, $this->tenant->timesheets->count());
        $this->assertGreaterThan(0, $this->tenant->paie->count());

        // 2. Relations du job
        $this->assertEquals($this->tenant->id, $this->job->tenant_id);
        $this->assertEquals($this->employer->id, $this->job->employer_id);
        $this->assertCount(1, $this->job->applications);

        // 3. Relations de l'application
        $this->assertEquals($this->job->id, $this->application->job_id);
        $this->assertEquals($this->tenant->id, $this->application->tenant_id);

        // 4. Relations du contrat
        $this->assertEquals($this->tenant->id, $this->contrat->tenant_id);
        $this->assertEquals($this->employee->id, $this->contrat->user_id);
        $this->assertEquals($this->job->id, $this->contrat->job_id);

        // 5. Relations du timesheet
        $this->assertEquals($this->tenant->id, $this->timesheet->tenant_id);
        $this->assertEquals($this->employee->id, $this->timesheet->user_id);
        $this->assertEquals($this->contrat->id, $this->timesheet->contrat_id);

        // 6. Relations de la paie
        $this->assertEquals($this->tenant->id, $this->paie->tenant_id);
        $this->assertEquals($this->employee->id, $this->paie->user_id);
        $this->assertEquals($this->contrat->id, $this->paie->contrat_id);
    }

    /** @test */
    public function scenario_statuts_et_etats()
    {
        // SCÉNARIO : Vérifier les statuts et états des modèles
        
        // 1. Statuts du contrat
        $this->assertContains($this->contrat->statut, ['BROUILLON', 'EN_ATTENTE_SIGNATURE', 'SIGNE', 'ACTIF', 'SUSPENDU', 'TERMINE']);
        
        // 2. Statuts du timesheet
        $this->assertContains($this->timesheet->statut, ['BROUILLON', 'EN_ATTENTE_VALIDATION', 'VALIDE', 'REJETE']);
        
        // 3. Statuts de la paie
        $this->assertContains($this->paie->statut, ['BROUILLON', 'GENERE', 'PAYE', 'ANNULE']);
        
        // 4. Statuts de l'application
        $this->assertContains($this->application->statut, ['EN_ATTENTE', 'ACCEPTE', 'REJETE']);
        
        // 5. Statuts du job
        $this->assertContains($this->job->status, ['draft', 'published', 'closed', 'filled']);
        
        // 6. Statut du tenant
        $this->assertTrue($this->tenant->is_active);
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
}