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

class IntegrationScenariosTest extends TestCase
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
    protected $employerToken;
    protected $employeeToken;

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

        // Obtenir des tokens d'authentification
        $this->employerToken = $this->getAuthToken($this->employer->email);
        $this->employeeToken = $this->getAuthToken($this->employee->email);
    }

    private function getAuthToken($email)
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $email,
            'password' => 'password'
        ]);
        
        return $response->json('data.token');
    }

    /** @test */
    public function scenario_complet_recrutement_et_embauche()
    {
        // SCÉNARIO : Un employeur crée une offre d'emploi, reçoit des candidatures,
        // embauche un candidat, crée un contrat, et suit les pointages

        // 1. L'employeur crée une nouvelle offre d'emploi
        $jobData = [
            'title' => 'Développeur Laravel Senior',
            'description' => 'Développement d\'applications web avec Laravel',
            'location' => 'Libreville',
            'type' => 'full_time',
            'salary_min' => 500000,
            'salary_max' => 800000,
            'salary_currency' => 'XAF',
            'requirements' => '3+ années d\'expérience Laravel',
            'skills_required' => ['Laravel', 'PHP', 'MySQL', 'Vue.js']
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson('/api/v1/jobs', $jobData);

        $response->assertStatus(200);
        $jobId = $response->json('data.id');

        // 2. Un candidat postule à l'offre
        $applicationData = [
            'job_id' => $jobId,
            'nom_candidat' => 'Jean Dupont',
            'email_candidat' => 'jean.dupont@example.com',
            'telephone_candidat' => '+241 01 23 45 67',
            'cv_url' => 'https://example.com/cv.pdf',
            'lettre_motivation' => 'Je suis très intéressé par ce poste...'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken
        ])->postJson('/api/v1/applications', $applicationData);

        $response->assertStatus(200);
        $applicationId = $response->json('data.id');

        // 3. L'employeur accepte la candidature
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->putJson("/api/v1/applications/{$applicationId}", [
            'statut' => 'ACCEPTE'
        ]);

        $response->assertStatus(200);

        // 4. L'employeur crée un contrat depuis la candidature
        $contratData = [
            'application_id' => $applicationId,
            'salaire_brut' => 600000,
            'date_debut' => now()->addDays(7)->format('Y-m-d'),
            'type_contrat' => 'CDI',
            'periode_essai_jours' => 90
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson('/api/v1/contrats/from-application', $contratData);

        $response->assertStatus(200);
        $contratId = $response->json('data.id');

        // 5. Le contrat est signé et activé
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson("/api/v1/contrats/{$contratId}/signer");

        $response->assertStatus(200);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson("/api/v1/contrats/{$contratId}/activer");

        $response->assertStatus(200);

        // 6. L'employé fait ses premiers pointages
        $timesheetData = [
            'contrat_id' => $contratId,
            'date_pointage' => now()->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '17:00',
            'duree_pause_minutes' => 60,
            'description_travail' => 'Développement de nouvelles fonctionnalités'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken
        ])->postJson('/api/v1/timesheets', $timesheetData);

        $response->assertStatus(200);
        $timesheetId = $response->json('data.id');

        // 7. L'employeur valide le pointage
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson("/api/v1/timesheets/{$timesheetId}/validate");

        $response->assertStatus(200);

        // 8. Génération de la paie mensuelle
        $paieData = [
            'user_id' => $this->employee->id,
            'periode_debut' => now()->startOfMonth()->format('Y-m-d'),
            'periode_fin' => now()->endOfMonth()->format('Y-m-d')
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson('/api/v1/paie/generate', $paieData);

        $response->assertStatus(200);
        $paieId = $response->json('data.id');

        // 9. Vérification des statistiques
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson('/api/v1/contrats/statistics/overview');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total',
                        'actifs',
                        'expires'
                    ]
                ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson('/api/v1/timesheets/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total',
                        'valides',
                        'en_attente'
                    ]
                ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson('/api/v1/paie/statistics');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total',
                        'payes',
                        'en_attente'
                    ]
                ]);

        // 10. Génération de documents PDF
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson("/api/v1/contrats/{$contratId}/pdf");

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/pdf');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson("/api/v1/paie/{$paieId}/pdf");

        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/pdf');
    }

    /** @test */
    public function scenario_gestion_heures_supplementaires_et_paie()
    {
        // SCÉNARIO : Un employé fait des heures supplémentaires, 
        // l'employeur valide, et la paie est calculée avec les majorations

        // 1. Créer un contrat actif
        $contrat = Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->employee->id,
            'salaire_brut' => 500000,
            'pays_code' => 'GA',
            'statut' => 'ACTIF'
        ]);

        // 2. L'employé fait des heures supplémentaires (10h au lieu de 8h)
        $timesheetData = [
            'contrat_id' => $contrat->id,
            'date_pointage' => now()->format('Y-m-d'),
            'heure_debut' => '08:00',
            'heure_fin' => '19:00', // 11h - 1h pause = 10h
            'duree_pause_minutes' => 60,
            'description_travail' => 'Projet urgent - heures supplémentaires'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken
        ])->postJson('/api/v1/timesheets', $timesheetData);

        $response->assertStatus(200);
        $timesheetId = $response->json('data.id');

        // 3. Vérifier que les heures supplémentaires sont calculées
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken
        ])->getJson("/api/v1/timesheets/{$timesheetId}");

        $response->assertStatus(200);
        $timesheet = $response->json('data');
        
        // 10h - 8h légales = 2h supplémentaires
        $this->assertEquals(120, $timesheet['heures_supplementaires_minutes']);

        // 4. L'employeur valide le pointage
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson("/api/v1/timesheets/{$timesheetId}/validate");

        $response->assertStatus(200);

        // 5. Générer la paie avec les heures supplémentaires
        $paieData = [
            'user_id' => $this->employee->id,
            'periode_debut' => now()->startOfMonth()->format('Y-m-d'),
            'periode_fin' => now()->endOfMonth()->format('Y-m-d')
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson('/api/v1/paie/generate', $paieData);

        $response->assertStatus(200);
        $paieId = $response->json('data.id');

        // 6. Vérifier que la paie inclut les majorations
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson("/api/v1/paie/{$paieId}");

        $response->assertStatus(200);
        $paie = $response->json('data');
        
        // Vérifier que les heures supplémentaires sont prises en compte
        $this->assertGreaterThan(0, $paie['heures_supplementaires_minutes']);
        $this->assertGreaterThan(0, $paie['montant_heures_sup']);
    }

    /** @test */
    public function scenario_pointage_geolocalise()
    {
        // SCÉNARIO : Un employé fait du pointage avec géolocalisation

        // 1. Pointage d'entrée avec géolocalisation
        $clockInData = [
            'contrat_id' => $this->contrat->id,
            'latitude' => 0.4162, // Libreville
            'longitude' => 9.4673,
            'adresse' => 'Libreville, Gabon'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken
        ])->postJson('/api/v1/timesheets/clock-in', $clockInData);

        $response->assertStatus(200);
        $timesheetId = $response->json('data.id');

        // 2. Vérifier que la géolocalisation est enregistrée
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken
        ])->getJson("/api/v1/timesheets/{$timesheetId}");

        $response->assertStatus(200);
        $timesheet = $response->json('data');
        
        $this->assertEquals(0.4162, $timesheet['latitude_debut']);
        $this->assertEquals(9.4673, $timesheet['longitude_debut']);

        // 3. Pointage de sortie
        $clockOutData = [
            'timesheet_id' => $timesheetId,
            'latitude' => 0.4162,
            'longitude' => 9.4673,
            'adresse' => 'Libreville, Gabon'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken
        ])->postJson('/api/v1/timesheets/clock-out', $clockOutData);

        $response->assertStatus(200);

        // 4. Vérifier que la distance est calculée (0 km car même lieu)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employeeToken
        ])->getJson("/api/v1/timesheets/{$timesheetId}");

        $response->assertStatus(200);
        $timesheet = $response->json('data');
        
        $this->assertEquals(0, $timesheet['distance_km']);
    }

    /** @test */
    public function scenario_export_comptable_et_paie()
    {
        // SCÉNARIO : L'employeur exporte les données pour la comptabilité

        // 1. Créer plusieurs pointages validés
        for ($i = 0; $i < 3; $i++) {
            $timesheet = Timesheet::factory()->create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $this->employee->id,
                'contrat_id' => $this->contrat->id,
                'date_pointage' => now()->subDays($i)->format('Y-m-d'),
                'statut' => 'VALIDE'
            ]);
        }

        // 2. Exporter les pointages pour la paie
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson('/api/v1/timesheets/export/payroll', [
            'periode_debut' => now()->subDays(7)->format('Y-m-d'),
            'periode_fin' => now()->format('Y-m-d')
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'timesheets'
                    ]
                ]);

        // 3. Créer plusieurs paies
        for ($i = 0; $i < 2; $i++) {
            Paie::factory()->create([
                'tenant_id' => $this->tenant->id,
                'user_id' => $this->employee->id,
                'contrat_id' => $this->contrat->id,
                'periode_debut' => now()->subMonths($i)->startOfMonth(),
                'periode_fin' => now()->subMonths($i)->endOfMonth(),
                'statut' => 'PAYE'
            ]);
        }

        // 4. Exporter les données comptables
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson('/api/v1/paie/export/accounting', [
            'periode_debut' => now()->subMonths(2)->format('Y-m-d'),
            'periode_fin' => now()->format('Y-m-d')
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'paie_records'
                    ]
                ]);
    }

    /** @test */
    public function scenario_gestion_modules_et_permissions()
    {
        // SCÉNARIO : L'administrateur gère les modules activés

        // 1. Lister les modules disponibles
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson('/api/v1/modules');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'modules'
                    ]
                ]);

        // 2. Activer un module
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson('/api/v1/modules/contrats/activate');

        $response->assertStatus(200);

        // 3. Vérifier le statut des modules
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->getJson('/api/v1/modules/active');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'modules'
                    ]
                ]);

        // 4. Désactiver un module
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->deleteJson('/api/v1/modules/contrats/deactivate');

        $response->assertStatus(200);
    }

    /** @test */
    public function scenario_workflow_complet_avec_erreurs()
    {
        // SCÉNARIO : Tester la gestion d'erreurs et les cas limites

        // 1. Tentative de création de contrat sans candidature
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson('/api/v1/contrats/from-application', [
            'application_id' => 99999, // ID inexistant
            'salaire_brut' => 500000
        ]);

        $response->assertStatus(404);

        // 2. Tentative de validation de pointage inexistant
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson('/api/v1/timesheets/99999/validate');

        $response->assertStatus(404);

        // 3. Tentative d'accès sans authentification
        $response = $this->getJson('/api/v1/contrats');
        $response->assertStatus(401);

        // 4. Tentative d'accès avec token invalide
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid_token'
        ])->getJson('/api/v1/contrats');

        $response->assertStatus(401);

        // 5. Validation de données manquantes
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->employerToken
        ])->postJson('/api/v1/jobs', []); // Données vides

        $response->assertStatus(422); // Erreur de validation
    }
}