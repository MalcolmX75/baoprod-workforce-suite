<?php

namespace BaoProd\Workforce\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use BaoProd\Workforce\Models\Tenant;
use BaoProd\Workforce\Services\ModuleService;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $module = null): Response
    {
        // Get tenant from subdomain or domain
        $tenant = $this->resolveTenant($request);
        
        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        if (!$tenant->is_active) {
            return response()->json(['error' => 'Tenant is inactive'], 403);
        }

        // Set tenant in request
        $request->attributes->set('tenant', $tenant);
        
        // Set tenant in app container
        app()->instance('tenant', $tenant);
        
        // Check module access if specified
        if ($module && !ModuleService::isActive($tenant, $module)) {
            return response()->json([
                'error' => 'Module not available',
                'module' => $module,
                'message' => 'This module is not activated for your tenant'
            ], 403);
        }

        return $next($request);
    }

    /**
     * Resolve tenant from request
     */
    private function resolveTenant(Request $request): ?Tenant
    {
        $host = $request->getHost();
        
        // For development, use default tenant
        if (app()->environment('local') || $host === 'localhost' || $host === '127.0.0.1') {
            return Tenant::first(); // Use first tenant for development
        }
        
        // Check if it's a subdomain
        if (strpos($host, '.') !== false) {
            $subdomain = explode('.', $host)[0];
            
            if ($subdomain !== 'www' && $subdomain !== 'api') {
                return Tenant::where('subdomain', $subdomain)->first();
            }
        }
        
        // Check by domain
        return Tenant::where('domain', $host)->first();
    }
}