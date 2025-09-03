<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use BaoProd\Workforce\Models\Timesheet;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\Job;
use Carbon\Carbon;

class TimesheetTest extends TestCase
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
            'modules' => ['core', 'timesheets'],
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
    public function it_can_create_a_timesheet()
    {
        $timesheetData = [
            'date_pointage' => now()->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '17:00',
            'heure_debut_pause' => '12:00',
            'heure_fin_pause' => '13:00',
            'duree_pause_minutes' => 60,
            'contrat_id' => $this->contrat->id,
            'job_id' => $this->job->id,
            'pays_code' => 'GA',
            'description_travail' => 'Test timesheet',
        ];

        $timesheet = new Timesheet();
        $timesheet->fill($timesheetData);
        $timesheet->tenant_id = $this->tenant->id;
        $timesheet->user_id = $this->user->id;
        $timesheet->modifie_par = $this->user->id;
        $timesheet->derniere_modification = now();
        
        // Appliquer la configuration du pays
        $timesheet->appliquerConfigurationPays();
        
        // Recalculer toutes les valeurs
        $timesheet->recalculerTout();
        
        $timesheet->statut = 'EN_ATTENTE_VALIDATION';
        $timesheet->save();

        $this->assertDatabaseHas('timesheets', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'contrat_id' => $this->contrat->id,
            'pays_code' => 'GA',
        ]);

        $this->assertEquals(480, $timesheet->heures_travaillees_minutes); // 8 heures
        $this->assertEquals(40, $timesheet->configuration_pays['heures_semaine']);
        $this->assertEquals(25.0, $timesheet->configuration_pays['taux_heures_sup']);
    }

    /** @test */
    public function it_can_calculate_worked_hours()
    {
        $timesheet = new Timesheet([
            'date_pointage' => now()->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '17:00',
            'duree_pause_minutes' => 60,
        ]);

        $heuresTravaillees = $timesheet->calculerHeuresTravaillees();
        
        $this->assertEquals(480, $heuresTravaillees); // 8 heures en minutes
    }

    /** @test */
    public function it_can_calculate_overtime_hours()
    {
        $timesheet = new Timesheet([
            'heures_travaillees_minutes' => 2520, // 42 heures (40 + 2 heures supplémentaires)
            'pays_code' => 'GA',
        ]);

        $heuresSup = $timesheet->calculerHeuresSupplementaires();
        
        $this->assertEquals(120, $heuresSup); // 2 heures supplémentaires (42h - 40h légales)
    }

    /** @test */
    public function it_can_calculate_night_hours()
    {
        $timesheet = new Timesheet([
            'date_pointage' => now()->format('Y-m-d'),
            'heure_debut' => '22:00',
            'heure_fin' => '06:00',
        ]);

        $heuresNuit = $timesheet->calculerHeuresNuit();
        
        $this->assertGreaterThan(0, $heuresNuit); // Doit y avoir des heures de nuit
    }

    /** @test */
    public function it_can_calculate_sunday_hours()
    {
        $sunday = Carbon::now()->next(Carbon::SUNDAY);
        
        $timesheet = new Timesheet([
            'date_pointage' => $sunday->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '17:00',
            'heures_travaillees_minutes' => 480,
        ]);

        $heuresDimanche = $timesheet->calculerHeuresDimanche();
        
        $this->assertEquals(480, $heuresDimanche); // Toutes les heures sont dimanche
    }

    /** @test */
    public function it_can_calculate_amounts()
    {
        $timesheet = new Timesheet([
            'heures_normales_minutes' => 480, // 8 heures
            'heures_supplementaires_minutes' => 120, // 2 heures
            'heures_nuit_minutes' => 60, // 1 heure
            'taux_horaire_normal' => 1000, // 1000 FCFA/heure
            'pays_code' => 'GA',
        ]);

        $timesheet->calculerMontants();

        $this->assertEquals(8000, $timesheet->montant_heures_normales); // 8 * 1000
        $this->assertEquals(2500, $timesheet->montant_heures_sup); // 2 * 1000 * 1.25
        $this->assertEquals(1300, $timesheet->montant_heures_nuit); // 1 * 1000 * 1.30
        $this->assertEquals(11800, $timesheet->montant_total); // Total
    }

    /** @test */
    public function it_can_calculate_distance()
    {
        $timesheet = new Timesheet([
            'latitude_debut' => 0.4162, // Libreville
            'longitude_debut' => 9.4673,
            'latitude_fin' => 0.4162,
            'longitude_fin' => 9.4673,
        ]);

        $distance = $timesheet->calculerDistance();
        
        $this->assertEquals(0, $distance); // Même position = 0 km
    }

    /** @test */
    public function it_can_apply_country_configuration()
    {
        $timesheet = new Timesheet([
            'pays_code' => 'GA',
        ]);

        $timesheet->appliquerConfigurationPays();

        $this->assertEquals(40, $timesheet->configuration_pays['heures_semaine']);
        $this->assertEquals(25, $timesheet->configuration_pays['taux_heures_sup']);
        $this->assertEquals(30, $timesheet->configuration_pays['taux_heures_nuit']);
    }

    /** @test */
    public function it_can_recalculate_everything()
    {
        $timesheet = new Timesheet([
            'date_pointage' => now()->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '18:00', // 10 heures
            'duree_pause_minutes' => 60,
            'pays_code' => 'GA',
            'taux_horaire_normal' => 1000,
        ]);

        $timesheet->recalculerTout();

        $this->assertEquals(540, $timesheet->heures_travaillees_minutes); // 9 heures
        $this->assertEquals(0, $timesheet->heures_supplementaires_minutes); // Pas d'heures sup (9h < 40h/semaine)
        $this->assertGreaterThan(0, $timesheet->montant_total); // Montant calculé
    }

    /** @test */
    public function it_can_check_timesheet_status()
    {
        $timesheet = new Timesheet(['statut' => 'VALIDE']);
        $this->assertTrue($timesheet->is_valide);

        $timesheet = new Timesheet(['statut' => 'EN_ATTENTE_VALIDATION']);
        $this->assertTrue($timesheet->is_en_attente);

        $timesheet = new Timesheet(['statut' => 'BROUILLON']);
        $this->assertTrue($timesheet->peutEtreModifie());

        $timesheet = new Timesheet(['statut' => 'EN_ATTENTE_VALIDATION']);
        $this->assertTrue($timesheet->peutEtreValide());
        $this->assertTrue($timesheet->peutEtreRejete());
    }

    /** @test */
    public function it_can_get_country_configuration()
    {
        $configGA = Timesheet::getConfigurationPays('GA');
        $configCM = Timesheet::getConfigurationPays('CM');

        $this->assertEquals(40, $configGA['heures_semaine']);
        $this->assertEquals(25, $configGA['taux_heures_sup']);
        $this->assertEquals(30, $configGA['taux_heures_nuit']);

        $this->assertEquals(40, $configCM['heures_semaine']);
        $this->assertEquals(25, $configCM['taux_heures_sup']);
        $this->assertEquals(30, $configCM['taux_heures_nuit']);
    }
}