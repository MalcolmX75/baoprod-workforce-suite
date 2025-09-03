<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiRoutesValidationTest extends TestCase
{
    /** @test */
    public function it_can_validate_all_58_api_routes_exist()
    {
        // Obtenir toutes les routes API
        $routes = \Route::getRoutes();
        $apiRoutes = [];
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            if (str_starts_with($uri, 'api/')) {
                $methods = $route->methods();
                foreach ($methods as $method) {
                    if (!in_array($method, ['HEAD', 'OPTIONS'])) {
                        $apiRoutes[] = [
                            'method' => $method,
                            'uri' => $uri,
                            'name' => $route->getName(),
                            'action' => $route->getActionName()
                        ];
                    }
                }
            }
        }
        
        // Vérifier qu'il y a au moins 58 routes (il peut y en avoir plus avec des routes de test)
        $this->assertGreaterThanOrEqual(58, count($apiRoutes), 'Il devrait y avoir au moins 58 routes API');
        
        // Grouper par module pour affichage
        $modules = [
            'Health' => [],
            'Auth' => [],
            'Jobs' => [],
            'Applications' => [],
            'Contrats' => [],
            'Timesheets' => [],
            'Paie' => [],
            'Modules' => [],
            'Other' => []
        ];
        
        foreach ($apiRoutes as $route) {
            $uri = $route['uri'];
            if ($uri === 'api/health') {
                $modules['Health'][] = $route;
            } elseif (str_contains($uri, 'auth')) {
                $modules['Auth'][] = $route;
            } elseif (str_contains($uri, 'jobs')) {
                $modules['Jobs'][] = $route;
            } elseif (str_contains($uri, 'applications')) {
                $modules['Applications'][] = $route;
            } elseif (str_contains($uri, 'contrats')) {
                $modules['Contrats'][] = $route;
            } elseif (str_contains($uri, 'timesheets')) {
                $modules['Timesheets'][] = $route;
            } elseif (str_contains($uri, 'paie')) {
                $modules['Paie'][] = $route;
            } elseif (str_contains($uri, 'modules')) {
                $modules['Modules'][] = $route;
            } else {
                $modules['Other'][] = $route;
            }
        }
        
        // Afficher le résumé
        echo "\n=== RÉSUMÉ DES 58 ENDPOINTS API ===\n";
        foreach ($modules as $module => $routes) {
            if (!empty($routes)) {
                echo "\n{$module}: " . count($routes) . " endpoints\n";
                foreach ($routes as $route) {
                    echo "  {$route['method']} {$route['uri']}\n";
                }
            }
        }
        
        // Vérifications spécifiques
        $this->assertCount(1, $modules['Health'], 'Il devrait y avoir 1 endpoint Health');
        $this->assertCount(7, $modules['Auth'], 'Il devrait y avoir 7 endpoints Auth');
        $this->assertCount(7, $modules['Jobs'], 'Il devrait y avoir 7 endpoints Jobs');
        $this->assertCount(6, $modules['Applications'], 'Il devrait y avoir 6 endpoints Applications');
        $this->assertCount(13, $modules['Contrats'], 'Il devrait y avoir 13 endpoints Contrats');
        $this->assertCount(11, $modules['Timesheets'], 'Il devrait y avoir 11 endpoints Timesheets');
        $this->assertCount(10, $modules['Paie'], 'Il devrait y avoir 10 endpoints Paie');
        $this->assertCount(4, $modules['Modules'], 'Il devrait y avoir 4 endpoints Modules');
        
        // Vérifier que tous les endpoints ont des contrôleurs valides
        foreach ($apiRoutes as $route) {
            $action = $route['action'];
            if (is_string($action) && str_contains($action, '@')) {
                [$controller, $method] = explode('@', $action);
                $this->assertTrue(
                    class_exists($controller), 
                    "Le contrôleur {$controller} n'existe pas pour la route {$route['method']} {$route['uri']}"
                );
            }
        }
    }

    /** @test */
    public function it_can_test_health_endpoint_works()
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
    public function it_can_validate_route_structure()
    {
        $routes = \Route::getRoutes();
        $apiRoutes = [];
        
        foreach ($routes as $route) {
            $uri = $route->uri();
            if (str_starts_with($uri, 'api/')) {
                $methods = $route->methods();
                foreach ($methods as $method) {
                    if (!in_array($method, ['HEAD', 'OPTIONS'])) {
                        $apiRoutes[] = [
                            'method' => $method,
                            'uri' => $uri,
                            'middleware' => $route->gatherMiddleware()
                        ];
                    }
                }
            }
        }
        
        // Vérifier que les routes protégées ont le bon middleware
        foreach ($apiRoutes as $route) {
            if ($route['uri'] !== 'api/health' && 
                !str_contains($route['uri'], 'auth/login') && 
                !str_contains($route['uri'], 'auth/register') &&
                !str_contains($route['uri'], 'test')) { // Exclure les routes de test
                
                $middleware = $route['middleware'];
                $this->assertTrue(
                    in_array('auth:sanctum', $middleware) || in_array('tenant', $middleware),
                    "La route {$route['method']} {$route['uri']} devrait avoir le middleware auth:sanctum ou tenant"
                );
            }
        }
    }
}