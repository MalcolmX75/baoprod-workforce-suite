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

class BusinessLogicIntegrationTest extends TestCase
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
    public function scenario_calcul_salaire_avec_heures_supplementaires()
    {
        // SCÉNARIO : Calcul automatique du salaire avec heures supplémentaires
        
        // 1. Créer un contrat avec salaire de base
        $contrat = new Contrat([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'salaire_brut' => 500000, // 500,000 XAF
            'pays_code' => 'GA',
            'statut' => 'ACTIF'
        ]);
        $contrat->save();

        // 2. Créer un timesheet avec heures supplémentaires
        $timesheet = new Timesheet([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'contrat_id' => $contrat->id,
            'date_pointage' => now()->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '19:00', // 11h - 1h pause = 10h
            'duree_pause_minutes' => 60,
            'pays_code' => 'GA',
            'statut' => 'VALIDE'
        ]);
        $timesheet->save();

        // 3. Recalculer toutes les valeurs
        $timesheet->recalculerTout();

        // 4. Vérifier les calculs
        $this->assertEquals(540, $timesheet->heures_travaillees_minutes); // 9h nettes
        $this->assertEquals(0, $timesheet->heures_supplementaires_minutes); // Pas d'heures sup (9h < 40h/semaine)
        $this->assertGreaterThan(0, $timesheet->montant_total);

        // 5. Créer une paie pour le mois
        $paie = new Paie([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'contrat_id' => $contrat->id,
            'periode_debut' => now()->startOfMonth(),
            'periode_fin' => now()->endOfMonth(),
            'salaire_brut_total' => 500000,
            'pays_code' => 'GA',
            'statut' => 'BROUILLON'
        ]);
        $paie->save();

        // 6. Appliquer la configuration du pays et recalculer
        $paie->appliquerConfigurationPays();
        $paie->recalculerTout();

        // 7. Vérifier les calculs de paie
        $this->assertEquals(500000, $paie->salaire_brut_total);
        $this->assertGreaterThan(0, $paie->charges_sociales_montant);
        $this->assertGreaterThan(0, $paie->cotisations_patronales);
        $this->assertGreaterThan(0, $paie->cotisations_salariales);
        $this->assertGreaterThan(0, $paie->salaire_net);
        $this->assertGreaterThan(0, $paie->net_a_payer);
    }

    /** @test */
    public function scenario_workflow_contrat_complet()
    {
        // SCÉNARIO : Workflow complet de création et gestion de contrat
        
        // 1. Créer un job
        $job = new Job([
            'tenant_id' => $this->tenant->id,
            'employer_id' => $this->employer->id,
            'title' => 'Développeur Senior',
            'description' => 'Développement d\'applications web',
            'location' => 'Libreville',
            'type' => 'full_time',
            'status' => 'published',
            'salary_min' => 600000,
            'salary_max' => 800000,
            'salary_currency' => 'XAF'
        ]);
        $job->save();

        // 2. Créer une candidature
        $application = new Application([
            'tenant_id' => $this->tenant->id,
            'job_id' => $job->id,
            'nom_candidat' => 'Jean Dupont',
            'email_candidat' => 'jean.dupont@example.com',
            'statut' => 'EN_ATTENTE'
        ]);
        $application->save();

        // 3. Accepter la candidature
        $application->statut = 'ACCEPTE';
        $application->save();

        // 4. Créer un contrat depuis la candidature
        $contrat = new Contrat([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'job_id' => $job->id,
            'salaire_brut' => 700000,
            'date_debut' => now()->addDays(7),
            'type_contrat' => 'CDI',
            'pays_code' => 'GA',
            'statut' => 'BROUILLON'
        ]);
        $contrat->save();

        // 5. Signer le contrat
        $contrat->statut = 'SIGNE';
        $contrat->date_signature = now();
        $contrat->save();

        // 6. Activer le contrat
        $contrat->statut = 'ACTIF';
        $contrat->date_activation = now();
        $contrat->save();

        // 7. Vérifier l'état final
        $this->assertEquals('ACTIF', $contrat->statut);
        $this->assertNotNull($contrat->date_signature);
        $this->assertNotNull($contrat->date_activation);
        $this->assertEquals(700000, $contrat->salaire_brut);
    }

    /** @test */
    public function scenario_calcul_charges_sociales_cemac()
    {
        // SCÉNARIO : Calcul des charges sociales selon la configuration CEMAC
        
        // 1. Créer une paie pour le Gabon
        $paie = new Paie([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'salaire_brut_total' => 1000000, // 1,000,000 XAF
            'pays_code' => 'GA',
            'statut' => 'BROUILLON'
        ]);
        $paie->save();

        // 2. Appliquer la configuration du Gabon
        $paie->appliquerConfigurationPays();

        // 3. Vérifier la configuration appliquée
        $config = $paie->configuration_pays;
        $this->assertEquals('GA', $config['region']);
        $this->assertEquals('XAF', $config['devise']);
        $this->assertEquals(28.0, $config['charges_sociales']);
        $this->assertEquals(21.5, $config['charges_employeur']);
        $this->assertEquals(6.5, $config['charges_salarie']);

        // 4. Calculer les charges sociales
        $charges = $paie->calculerChargesSociales();
        $this->assertEquals(280000, $charges); // 1,000,000 * 0.28

        // 5. Vérifier la répartition
        $this->assertEquals(215000, $paie->cotisations_patronales); // 1,000,000 * 0.215
        $this->assertEquals(65000, $paie->cotisations_salariales); // 1,000,000 * 0.065

        // 6. Calculer l'impôt sur le revenu
        $impot = $paie->calculerImpotSurRevenu();
        $this->assertGreaterThan(0, $impot); // Doit y avoir de l'impôt sur 1M XAF

        // 7. Calculer le salaire net
        $salaireNet = $paie->calculerSalaireNet();
        $this->assertLessThan(1000000, $salaireNet); // Moins que le brut
        $this->assertGreaterThan(0, $salaireNet); // Mais positif

        // 8. Calculer le net à payer
        $netAPayer = $paie->calculerNetAPayer();
        $this->assertLessThan($salaireNet, $netAPayer); // Moins que le salaire net
        $this->assertGreaterThan(0, $netAPayer); // Mais positif
    }

    /** @test */
    public function scenario_pointage_avec_geolocalisation()
    {
        // SCÉNARIO : Pointage avec calcul de distance
        
        // 1. Créer un contrat actif
        $contrat = new Contrat([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'salaire_brut' => 500000,
            'pays_code' => 'GA',
            'statut' => 'ACTIF'
        ]);
        $contrat->save();

        // 2. Créer un timesheet avec géolocalisation
        $timesheet = new Timesheet([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'contrat_id' => $contrat->id,
            'date_pointage' => now()->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '17:00',
            'latitude_debut' => 0.4162, // Libreville
            'longitude_debut' => 9.4673,
            'latitude_fin' => 0.4162, // Même lieu
            'longitude_fin' => 9.4673,
            'adresse_debut' => 'Libreville, Gabon',
            'adresse_fin' => 'Libreville, Gabon',
            'pays_code' => 'GA',
            'statut' => 'VALIDE'
        ]);
        $timesheet->save();

        // 3. Calculer la distance
        $distance = $timesheet->calculerDistance();
        $this->assertEquals(0, $distance); // Même lieu = 0 km

        // 4. Recalculer tout
        $timesheet->recalculerTout();

        // 5. Vérifier les calculs
        $this->assertEquals(480, $timesheet->heures_travaillees_minutes); // 8h
        $this->assertEquals(0, $timesheet->distance_km); // 0 km
        $this->assertGreaterThan(0, $timesheet->montant_total);
    }

    /** @test */
    public function scenario_generation_documents_contrat()
    {
        // SCÉNARIO : Génération de documents de contrat
        
        // 1. Créer un contrat complet
        $contrat = new Contrat([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'salaire_brut' => 600000,
            'date_debut' => now()->addDays(7),
            'type_contrat' => 'CDI',
            'periode_essai_jours' => 90,
            'pays_code' => 'GA',
            'statut' => 'ACTIF'
        ]);
        $contrat->save();

        // 2. Appliquer la configuration du pays
        $contrat->appliquerConfigurationPays();

        // 3. Vérifier que la configuration est appliquée
        $this->assertNotNull($contrat->configuration_pays);
        $this->assertEquals('GA', $contrat->configuration_pays['region']);

        // 4. Calculer le salaire net
        $salaireNet = $contrat->calculerSalaireNet();
        $this->assertLessThan(600000, $salaireNet);
        $this->assertGreaterThan(0, $salaireNet);

        // 5. Calculer le taux horaire
        $tauxHoraire = $contrat->calculerTauxHoraire();
        $this->assertGreaterThan(0, $tauxHoraire);

        // 6. Vérifier le statut du contrat
        $this->assertTrue($contrat->estActif());
        $this->assertFalse($contrat->estExpire());
    }

    /** @test */
    public function scenario_validation_donnees_metier()
    {
        // SCÉNARIO : Validation des données métier et contraintes
        
        // 1. Vérifier qu'un contrat ne peut pas être créé sans salaire
        $contrat = new Contrat([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'salaire_brut' => 0, // Salaire invalide
            'pays_code' => 'GA'
        ]);
        
        $this->assertFalse($contrat->save()); // Doit échouer

        // 2. Vérifier qu'un timesheet ne peut pas être créé sans heures
        $timesheet = new Timesheet([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'date_pointage' => now()->format('Y-m-d'),
            'heure_debut' => null, // Heure invalide
            'heure_fin' => null,
            'pays_code' => 'GA'
        ]);
        
        $this->assertFalse($timesheet->save()); // Doit échouer

        // 3. Vérifier qu'une paie ne peut pas être créée sans période
        $paie = new Paie([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'salaire_brut_total' => 500000,
            'periode_debut' => null, // Période invalide
            'periode_fin' => null,
            'pays_code' => 'GA'
        ]);
        
        $this->assertFalse($paie->save()); // Doit échouer

        // 4. Vérifier les contraintes de dates
        $paie = new Paie([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'salaire_brut_total' => 500000,
            'periode_debut' => now()->endOfMonth(), // Début après fin
            'periode_fin' => now()->startOfMonth(),
            'pays_code' => 'GA'
        ]);
        
        $this->assertFalse($paie->save()); // Doit échouer
    }

    /** @test */
    public function scenario_calculs_complexes_paie()
    {
        // SCÉNARIO : Calculs complexes de paie avec tous les éléments
        
        // 1. Créer une paie avec un salaire élevé pour tester l'impôt progressif
        $paie = new Paie([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'salaire_brut_total' => 2000000, // 2,000,000 XAF (salaire élevé)
            'pays_code' => 'GA',
            'statut' => 'BROUILLON'
        ]);
        $paie->save();

        // 2. Appliquer la configuration et recalculer
        $paie->appliquerConfigurationPays();
        $paie->recalculerTout();

        // 3. Vérifier que tous les calculs sont cohérents
        $this->assertEquals(2000000, $paie->salaire_brut_total);
        
        // Charges sociales
        $this->assertGreaterThan(0, $paie->charges_sociales_montant);
        $this->assertGreaterThan(0, $paie->cotisations_patronales);
        $this->assertGreaterThan(0, $paie->cotisations_salariales);
        
        // Impôt progressif
        $this->assertGreaterThan(0, $paie->impot_sur_revenu);
        
        // Salaires
        $this->assertGreaterThan(0, $paie->salaire_net);
        $this->assertGreaterThan(0, $paie->net_a_payer);
        
        // Cohérence des calculs
        $this->assertLessThan($paie->salaire_brut_total, $paie->salaire_net);
        $this->assertLessThan($paie->salaire_net, $paie->net_a_payer);
        
        // 4. Vérifier que le total des charges + salaire net = salaire brut
        $totalCharges = $paie->cotisations_salariales + $paie->impot_sur_revenu;
        $this->assertEquals($paie->salaire_brut_total, $paie->salaire_net + $totalCharges);
    }
}