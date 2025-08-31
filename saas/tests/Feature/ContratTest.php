<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use BaoProd\Workforce\Models\User;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Models\Contrat;
use BaoProd\Workforce\Models\Job;
use Laravel\Sanctum\Sanctum;

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
        $this->tenant = Tenant::factory()->create([
            'name' => 'Test Tenant',
            'country_code' => 'GA',
            'modules' => ['contrats'],
        ]);
        
        // Créer un utilisateur de test
        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'type' => 'employer',
        ]);
        
        // Créer un job de test
        $this->job = Job::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
        ]);
        
        // Authentifier l'utilisateur
        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function it_can_list_contrats()
    {
        // Créer quelques contrats de test
        Contrat::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/contrats?tenant_id=' . $this->tenant->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'numero_contrat',
                                'type_contrat',
                                'statut',
                                'salaire_brut',
                                'pays_code',
                                'user',
                                'created_by',
                            ]
                        ]
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_can_create_a_contrat()
    {
        $contratData = [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'job_id' => $this->job->id,
            'type_contrat' => 'CDI',
            'date_debut' => now()->addDays(7)->format('Y-m-d'),
            'salaire_brut' => 150000,
            'pays_code' => 'GA',
            'description' => 'Contrat de test',
        ];

        $response = $this->postJson('/api/v1/contrats', $contratData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'numero_contrat',
                        'type_contrat',
                        'statut',
                        'salaire_brut',
                        'salaire_net',
                        'pays_code',
                        'user',
                        'job',
                        'created_by',
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('contrats', [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type_contrat' => 'CDI',
            'pays_code' => 'GA',
        ]);
    }

    /** @test */
    public function it_can_show_a_contrat()
    {
        $contrat = Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/v1/contrats/' . $contrat->id . '?tenant_id=' . $this->tenant->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'id',
                        'numero_contrat',
                        'type_contrat',
                        'statut',
                        'salaire_brut',
                        'user',
                        'job',
                        'created_by',
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_can_update_a_contrat()
    {
        $contrat = Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'statut' => 'BROUILLON',
        ]);

        $updateData = [
            'salaire_brut' => 200000,
            'description' => 'Contrat mis à jour',
        ];

        $response = $this->putJson('/api/v1/contrats/' . $contrat->id . '?tenant_id=' . $this->tenant->id, $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);

        $this->assertDatabaseHas('contrats', [
            'id' => $contrat->id,
            'salaire_brut' => 200000,
            'description' => 'Contrat mis à jour',
        ]);
    }

    /** @test */
    public function it_can_delete_a_contrat()
    {
        $contrat = Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'statut' => 'BROUILLON',
        ]);

        $response = $this->deleteJson('/api/v1/contrats/' . $contrat->id . '?tenant_id=' . $this->tenant->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);

        $this->assertDatabaseMissing('contrats', [
            'id' => $contrat->id,
        ]);
    }

    /** @test */
    public function it_can_sign_a_contrat()
    {
        $contrat = Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'statut' => 'EN_ATTENTE_SIGNATURE',
        ]);

        $signatureData = [
            'signature_employe' => true,
            'signature_employeur' => true,
        ];

        $response = $this->postJson('/api/v1/contrats/' . $contrat->id . '/signer?tenant_id=' . $this->tenant->id, $signatureData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);

        $this->assertDatabaseHas('contrats', [
            'id' => $contrat->id,
            'statut' => 'SIGNE',
        ]);
    }

    /** @test */
    public function it_can_activate_a_contrat()
    {
        $contrat = Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'statut' => 'SIGNE',
        ]);

        $response = $this->postJson('/api/v1/contrats/' . $contrat->id . '/activer?tenant_id=' . $this->tenant->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);

        $this->assertDatabaseHas('contrats', [
            'id' => $contrat->id,
            'statut' => 'ACTIF',
        ]);
    }

    /** @test */
    public function it_can_terminate_a_contrat()
    {
        $contrat = Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'statut' => 'ACTIF',
        ]);

        $terminationData = [
            'date_fin_effective' => now()->addDays(30)->format('Y-m-d'),
            'motif_fin' => 'Fin de mission',
        ];

        $response = $this->postJson('/api/v1/contrats/' . $contrat->id . '/terminer?tenant_id=' . $this->tenant->id, $terminationData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data',
                    'message'
                ]);

        $this->assertDatabaseHas('contrats', [
            'id' => $contrat->id,
            'statut' => 'TERMINE',
        ]);
    }

    /** @test */
    public function it_can_get_contrat_statistics()
    {
        // Créer des contrats avec différents statuts
        Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'statut' => 'ACTIF',
            'type_contrat' => 'CDI',
            'pays_code' => 'GA',
        ]);

        Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'statut' => 'SIGNE',
            'type_contrat' => 'CDD',
            'pays_code' => 'CM',
        ]);

        $response = $this->getJson('/api/v1/contrats/statistics/overview?tenant_id=' . $this->tenant->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'total',
                        'par_statut',
                        'par_type',
                        'par_pays',
                        'actifs',
                        'en_periode_essai',
                    ],
                    'message'
                ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_contrat()
    {
        $response = $this->postJson('/api/v1/contrats', []);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors' => [
                        'user_id',
                        'type_contrat',
                        'date_debut',
                        'salaire_brut',
                        'pays_code',
                    ]
                ]);
    }

    /** @test */
    public function it_cannot_modify_contrat_when_not_in_draft_status()
    {
        $contrat = Contrat::factory()->create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'statut' => 'SIGNE',
        ]);

        $updateData = [
            'salaire_brut' => 200000,
        ];

        $response = $this->putJson('/api/v1/contrats/' . $contrat->id . '?tenant_id=' . $this->tenant->id, $updateData);

        $response->assertStatus(403)
                ->assertJsonStructure([
                    'success',
                    'message'
                ]);
    }

    /** @test */
    public function it_applies_country_configuration_when_creating_contrat()
    {
        $contratData = [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type_contrat' => 'CDI',
            'date_debut' => now()->addDays(7)->format('Y-m-d'),
            'salaire_brut' => 150000,
            'pays_code' => 'GA', // Gabon
        ];

        $response = $this->postJson('/api/v1/contrats', $contratData);

        $response->assertStatus(201);

        $contrat = Contrat::latest()->first();
        
        // Vérifier que la configuration Gabon a été appliquée
        $this->assertEquals(40, $contrat->heures_semaine);
        $this->assertEquals(28.0, $contrat->charges_sociales_pourcentage);
        $this->assertEquals(80000, $contrat->smig);
    }

    /** @test */
    public function it_calculates_net_salary_correctly()
    {
        $contratData = [
            'tenant_id' => $this->tenant->id,
            'user_id' => $this->user->id,
            'type_contrat' => 'CDI',
            'date_debut' => now()->addDays(7)->format('Y-m-d'),
            'salaire_brut' => 100000,
            'pays_code' => 'GA', // 28% charges
        ];

        $response = $this->postJson('/api/v1/contrats', $contratData);

        $response->assertStatus(201);

        $contrat = Contrat::latest()->first();
        
        // Salaire net = 100000 - (100000 * 0.28) = 72000
        $this->assertEquals(72000, $contrat->salaire_net);
        $this->assertEquals(28000, $contrat->charges_sociales_montant);
    }
}
