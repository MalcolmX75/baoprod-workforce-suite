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

class ApiValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $user;
    protected $job;
    protected $contrat;
    protected $timesheet;
    protected $paie;
    protected $application;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Utiliser les seeders existants
        $this->seed();
        
        // Récupérer les données des seeders
        $this->tenant = Tenant::first();
        $this->user = User::first();
        $this->job = Job::first();
        $this->contrat = Contrat::first();
        $this->timesheet = Timesheet::first();
        $this->paie = Paie::first();
        $this->application = Application::first();

        // Obtenir un token d'authentification
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);
        
        $this->token = $response->json('data.token');
    }

    /** @test */
    public function it_can_test_health_endpoint()
    {
        $response = $this->getJson('/api/health');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'timestamp',
                    'version'
                ]);
    }

    /** @test */
    public function it_can_test_auth_endpoints()
    {
        // Test login
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'user@test.com',
            'password' => 'password'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user',
                        'token'
                    ]
                ]);

        // Test me endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/auth/me');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'nom',
                        'email',
                        'role'
                    ]
                ]);

        // Test logout
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/auth/logout');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_test_job_endpoints()
    {
        // Test index
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/jobs');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'titre',
                                'description',
                                'statut'
                            ]
                        ]
                    ]
                ]);

        // Test show
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/jobs/' . $this->job->id);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'titre',
                        'description',
                        'statut'
                    ]
                ]);

        // Test update
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/v1/jobs/' . $this->job->id, [
            'titre' => 'Updated Job Title',
            'description' => 'Updated Description'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /** @test */
    public function it_can_test_application_endpoints()
    {
        // Test index
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/applications');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'nom_candidat',
                                'email_candidat',
                                'statut'
                            ]
                        ]
                    ]
                ]);

        // Test show
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/applications/' . $this->application->id);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'nom_candidat',
                        'email_candidat',
                        'statut'
                    ]
                ]);

        // Test update
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/v1/applications/' . $this->application->id, [
            'statut' => 'ACCEPTE'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);
    }

    /** @test */
    public function it_can_test_contrat_endpoints()
    {
        // Test index
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/contrats');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'salaire_brut',
                                'statut'
                            ]
                        ]
                    ]
                ]);

        // Test show
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/contrats/' . $this->contrat->id);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'salaire_brut',
                        'statut'
                    ]
                ]);

        // Test update
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/v1/contrats/' . $this->contrat->id, [
            'salaire_brut' => 120000
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test signature
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/contrats/' . $this->contrat->id . '/signer');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test activation
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/contrats/' . $this->contrat->id . '/activer');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test génération HTML
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/contrats/' . $this->contrat->id . '/html');
        
        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'text/html; charset=UTF-8');

        // Test génération PDF
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/contrats/' . $this->contrat->id . '/pdf');
        
        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/pdf');

        // Test création depuis application
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/contrats/from-application', [
            'application_id' => $this->application->id,
            'salaire_brut' => 100000,
            'date_debut' => now()->format('Y-m-d')
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test templates disponibles
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/contrats/templates/available');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'templates'
                    ]
                ]);

        // Test statistiques
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
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
    }

    /** @test */
    public function it_can_test_timesheet_endpoints()
    {
        // Test index
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/timesheets');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'date_pointage',
                                'heure_debut',
                                'heure_fin',
                                'statut'
                            ]
                        ]
                    ]
                ]);

        // Test show
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/timesheets/' . $this->timesheet->id);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'date_pointage',
                        'heure_debut',
                        'heure_fin',
                        'statut'
                    ]
                ]);

        // Test update
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/v1/timesheets/' . $this->timesheet->id, [
            'heure_fin' => '18:00'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test validation
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/timesheets/' . $this->timesheet->id . '/validate');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test rejet
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/timesheets/' . $this->timesheet->id . '/reject', [
            'commentaire' => 'Test rejection'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test pointage entrée
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/timesheets/clock-in', [
            'contrat_id' => $this->contrat->id,
            'latitude' => 0.4162,
            'longitude' => 9.4673
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test pointage sortie
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/timesheets/clock-out', [
            'timesheet_id' => $this->timesheet->id,
            'latitude' => 0.4162,
            'longitude' => 9.4673
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test export pour paie
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/timesheets/export/payroll', [
            'periode_debut' => now()->startOfMonth()->format('Y-m-d'),
            'periode_fin' => now()->endOfMonth()->format('Y-m-d')
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'timesheets'
                    ]
                ]);

        // Test statistiques
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
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
    }

    /** @test */
    public function it_can_test_paie_endpoints()
    {
        // Test index
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/paie');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'numero_bulletin',
                                'periode_debut',
                                'periode_fin',
                                'statut'
                            ]
                        ]
                    ]
                ]);

        // Test show
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/paie/' . $this->paie->id);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'numero_bulletin',
                        'periode_debut',
                        'periode_fin',
                        'statut'
                    ]
                ]);

        // Test update
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/v1/paie/' . $this->paie->id, [
            'salaire_brut_total' => 120000
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test génération depuis timesheets
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/paie/generate', [
            'user_id' => $this->user->id,
            'periode_debut' => now()->startOfMonth()->format('Y-m-d'),
            'periode_fin' => now()->endOfMonth()->format('Y-m-d')
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test marquer comme payé
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/paie/' . $this->paie->id . '/mark-paid');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test génération PDF
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/paie/' . $this->paie->id . '/pdf');
        
        $response->assertStatus(200)
                ->assertHeader('Content-Type', 'application/pdf');

        // Test export comptable
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/paie/export/accounting', [
            'periode_debut' => now()->startOfMonth()->format('Y-m-d'),
            'periode_fin' => now()->endOfMonth()->format('Y-m-d')
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'paie_records'
                    ]
                ]);

        // Test statistiques
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
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
    }

    /** @test */
    public function it_can_test_modules_endpoints()
    {
        // Test list modules
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/modules');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'modules'
                    ]
                ]);

        // Test activate module
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/modules/activate', [
            'module' => 'contrats'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test deactivate module
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/v1/modules/deactivate', [
            'module' => 'contrats'
        ]);
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ]);

        // Test module status
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/modules/status');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'modules'
                    ]
                ]);
    }
}