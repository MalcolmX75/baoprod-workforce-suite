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

        // Seed the database with roles, permissions, and users
        $this->seed();

        $this->tenant = Tenant::first();
        $this->app->instance('tenant', $this->tenant);

        // Get the admin user created by the seeder
        $this->user = User::where('email', 'admin@baoprod-gabon.com')->first();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token');

        if (!$this->token) {
            $this->fail('Authentication token could not be generated. Response: ' . $response->getContent());
        }
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
        // Test me endpoint (user is already logged in via setUp)
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/v1/auth/me');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => [
                            'id',
                            'first_name',
                            'last_name',
                            'email',
                            'type'
                        ],
                        'tenant',
                        'modules'
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
                                'title', // Corrected from 'titre'
                                'description',
                                'status'
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
                        'title', // Corrected from 'titre'
                        'description',
                        'status'
                    ]
                ]);

        // Test update
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/v1/jobs/' . $this->job->id, [
            'title' => 'Updated Job Title', // Corrected from 'titre'
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
                                'job_id',
                                'candidate_id',
                                'status'
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
                        'job_id',
                        'candidate_id',
                        'status'
                    ]
                ]);

        // Test update
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/v1/applications/' . $this->application->id, [
            'status' => 'reviewed' // Corrected from 'ACCEPTE' to a valid status
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
                                'user_id',
                                'job_id',
                                'statut',
                                'salaire_brut'
                            ]
                        ]
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