<?php

namespace BaoProd\Workforce\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use BaoProd\Workforce\Services\ModuleManager;
use Symfony\Component\HttpFoundation\Response;

class CheckModuleActive
{
    protected ModuleManager $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $moduleCode, string $feature = null): Response
    {
        // Skip check for super admin
        if (auth()->check() && auth()->user()->type === 'super_admin') {
            return $next($request);
        }

        // Check if module is active
        if (!$this->moduleManager->isModuleActive($moduleCode)) {
            return $this->handleInactiveModule($request, $moduleCode);
        }

        // Check specific feature if provided
        if ($feature && !$this->moduleManager->hasFeature($moduleCode, $feature)) {
            return $this->handleInactiveFeature($request, $moduleCode, $feature);
        }

        return $next($request);
    }

    /**
     * Handle request when module is inactive
     */
    private function handleInactiveModule(Request $request, string $moduleCode): Response
    {
        $moduleName = ModuleManager::AVAILABLE_MODULES[$moduleCode]['name'] ?? $moduleCode;

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Module not active',
                'message' => "Le module '{$moduleName}' n'est pas activé pour votre organisation.",
                'module_code' => $moduleCode,
            ], 403);
        }

        return redirect()
            ->route('dashboard')
            ->with('error', "Le module '{$moduleName}' n'est pas activé pour votre organisation. Contactez votre administrateur.");
    }

    /**
     * Handle request when feature is inactive
     */
    private function handleInactiveFeature(Request $request, string $moduleCode, string $feature): Response
    {
        $moduleName = ModuleManager::AVAILABLE_MODULES[$moduleCode]['name'] ?? $moduleCode;

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Feature not available',
                'message' => "La fonctionnalité '{$feature}' du module '{$moduleName}' n'est pas disponible dans votre abonnement.",
                'module_code' => $moduleCode,
                'feature' => $feature,
            ], 403);
        }

        return redirect()
            ->back()
            ->with('warning', "La fonctionnalité '{$feature}' n'est pas disponible dans votre abonnement actuel.");
    }
}