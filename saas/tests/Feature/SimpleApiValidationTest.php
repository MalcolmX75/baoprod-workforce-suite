<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SimpleApiValidationTest extends TestCase
{
    use RefreshDatabase;

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
    public function it_can_test_auth_login_endpoint_exists()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
        
        // On s'attend à une erreur 401 (non authentifié) mais pas à une erreur 404 (endpoint inexistant)
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_test_auth_register_endpoint_exists()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
        
        // On s'attend à une erreur de validation ou autre, mais pas à une erreur 404
        $this->assertNotEquals(404, $response->status());
    }

    /** @test */
    public function it_can_test_jobs_endpoint_exists()
    {
        $response = $this->getJson('/api/v1/jobs');
        
        // On s'attend à une erreur 401 (non authentifié) mais pas à une erreur 404
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_test_applications_endpoint_exists()
    {
        $response = $this->getJson('/api/v1/applications');
        
        // On s'attend à une erreur 401 (non authentifié) mais pas à une erreur 404
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_test_contrats_endpoint_exists()
    {
        $response = $this->getJson('/api/v1/contrats');
        
        // On s'attend à une erreur 401 (non authentifié) mais pas à une erreur 404
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_test_timesheets_endpoint_exists()
    {
        $response = $this->getJson('/api/v1/timesheets');
        
        // On s'attend à une erreur 401 (non authentifié) mais pas à une erreur 404
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_test_paie_endpoint_exists()
    {
        $response = $this->getJson('/api/v1/paie');
        
        // On s'attend à une erreur 401 (non authentifié) mais pas à une erreur 404
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_test_modules_endpoint_exists()
    {
        $response = $this->getJson('/api/v1/modules');
        
        // On s'attend à une erreur 401 (non authentifié) mais pas à une erreur 404
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_test_all_58_endpoints_exist()
    {
        $endpoints = [
            // Health
            ['GET', '/api/health'],
            
            // Auth (7 endpoints)
            ['POST', '/api/v1/auth/login'],
            ['POST', '/api/v1/auth/logout'],
            ['GET', '/api/v1/auth/me'],
            ['PUT', '/api/v1/auth/password'],
            ['PUT', '/api/v1/auth/profile'],
            ['POST', '/api/v1/auth/refresh'],
            ['POST', '/api/v1/auth/register'],
            
            // Jobs (6 endpoints)
            ['GET', '/api/v1/jobs'],
            ['POST', '/api/v1/jobs'],
            ['GET', '/api/v1/jobs/1'],
            ['PUT', '/api/v1/jobs/1'],
            ['DELETE', '/api/v1/jobs/1'],
            ['GET', '/api/v1/jobs/statistics'],
            
            // Applications (5 endpoints)
            ['GET', '/api/v1/applications'],
            ['POST', '/api/v1/applications'],
            ['GET', '/api/v1/applications/1'],
            ['PUT', '/api/v1/applications/1'],
            ['DELETE', '/api/v1/applications/1'],
            
            // Contrats (13 endpoints)
            ['GET', '/api/v1/contrats'],
            ['POST', '/api/v1/contrats'],
            ['GET', '/api/v1/contrats/1'],
            ['PUT', '/api/v1/contrats/1'],
            ['DELETE', '/api/v1/contrats/1'],
            ['POST', '/api/v1/contrats/1/signer'],
            ['POST', '/api/v1/contrats/1/activer'],
            ['POST', '/api/v1/contrats/1/terminer'],
            ['GET', '/api/v1/contrats/1/html'],
            ['GET', '/api/v1/contrats/1/pdf'],
            ['POST', '/api/v1/contrats/from-application'],
            ['GET', '/api/v1/contrats/templates/available'],
            ['GET', '/api/v1/contrats/statistics/overview'],
            
            // Timesheets (11 endpoints)
            ['GET', '/api/v1/timesheets'],
            ['POST', '/api/v1/timesheets'],
            ['GET', '/api/v1/timesheets/1'],
            ['PUT', '/api/v1/timesheets/1'],
            ['DELETE', '/api/v1/timesheets/1'],
            ['POST', '/api/v1/timesheets/1/validate'],
            ['POST', '/api/v1/timesheets/1/reject'],
            ['POST', '/api/v1/timesheets/clock-in'],
            ['POST', '/api/v1/timesheets/clock-out'],
            ['GET', '/api/v1/timesheets/export/payroll'],
            ['GET', '/api/v1/timesheets/statistics'],
            
            // Paie (10 endpoints)
            ['GET', '/api/v1/paie'],
            ['POST', '/api/v1/paie'],
            ['GET', '/api/v1/paie/1'],
            ['PUT', '/api/v1/paie/1'],
            ['DELETE', '/api/v1/paie/1'],
            ['POST', '/api/v1/paie/generate'],
            ['POST', '/api/v1/paie/1/mark-paid'],
            ['GET', '/api/v1/paie/1/pdf'],
            ['GET', '/api/v1/paie/export/accounting'],
            ['GET', '/api/v1/paie/statistics'],
            
            // Modules (4 endpoints)
            ['GET', '/api/v1/modules'],
            ['POST', '/api/v1/modules/activate'],
            ['POST', '/api/v1/modules/deactivate'],
            ['GET', '/api/v1/modules/status'],
        ];

        $this->assertCount(58, $endpoints, 'Il devrait y avoir exactement 58 endpoints');

        foreach ($endpoints as $endpoint) {
            [$method, $url] = $endpoint;
            
            $response = $this->call($method, $url);
            
            // Vérifier que l'endpoint existe (pas de 404)
            $this->assertNotEquals(404, $response->status(), 
                "L'endpoint {$method} {$url} n'existe pas (404)");
            
            // Vérifier que l'endpoint retourne une réponse valide (pas d'erreur 500)
            $this->assertLessThan(500, $response->status(), 
                "L'endpoint {$method} {$url} a une erreur serveur (500+)");
        }
    }
}