<?php

namespace BaoProd\Workforce\Http\Controllers\Api;

use BaoProd\Workforce\Http\Controllers\Controller;
use BaoProd\Workforce\Services\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ModuleController extends Controller
{
    /**
     * Get all available modules
     */
    public function index(): JsonResponse
    {
        $modules = ModuleService::getAvailableModules();

        return response()->json([
            'success' => true,
            'data' => $modules
        ]);
    }

    /**
     * Get active modules for current tenant
     */
    public function active(): JsonResponse
    {
        $tenant = app('tenant');
        $activeModules = ModuleService::getActiveModules($tenant);
        $availableModules = ModuleService::getAvailableModules();
        
        $modules = [];
        foreach ($activeModules as $moduleKey) {
            if (isset($availableModules[$moduleKey])) {
                $modules[$moduleKey] = $availableModules[$moduleKey];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'modules' => $modules,
                'monthly_cost' => ModuleService::calculateMonthlyCost($tenant),
                'tenant_type' => $this->getTenantType($tenant),
            ]
        ]);
    }

    /**
     * Activate a module for current tenant
     */
    public function activate(Request $request, string $module): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        // Only admins can activate modules
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can activate modules'
            ], 403);
        }

        $availableModules = ModuleService::getAvailableModules();
        
        if (!isset($availableModules[$module])) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found'
            ], 404);
        }

        if (ModuleService::isActive($tenant, $module)) {
            return response()->json([
                'success' => false,
                'message' => 'Module is already active'
            ], 400);
        }

        $success = ModuleService::activateModule($tenant, $module);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Module activated successfully',
                'data' => [
                    'module' => $module,
                    'module_info' => $availableModules[$module],
                    'new_monthly_cost' => ModuleService::calculateMonthlyCost($tenant),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to activate module'
        ], 500);
    }

    /**
     * Deactivate a module for current tenant
     */
    public function deactivate(Request $request, string $module): JsonResponse
    {
        $tenant = app('tenant');
        $user = $request->user();

        // Only admins can deactivate modules
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can deactivate modules'
            ], 403);
        }

        if ($module === 'core') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate core module'
            ], 400);
        }

        if (!ModuleService::isActive($tenant, $module)) {
            return response()->json([
                'success' => false,
                'message' => 'Module is not active'
            ], 400);
        }

        $success = ModuleService::deactivateModule($tenant, $module);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Module deactivated successfully',
                'data' => [
                    'module' => $module,
                    'new_monthly_cost' => ModuleService::calculateMonthlyCost($tenant),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to deactivate module'
        ], 500);
    }

    /**
     * Get module features
     */
    public function features(string $module): JsonResponse
    {
        $availableModules = ModuleService::getAvailableModules();
        
        if (!isset($availableModules[$module])) {
            return response()->json([
                'success' => false,
                'message' => 'Module not found'
            ], 404);
        }

        $features = ModuleService::getModuleFeatures($module);

        return response()->json([
            'success' => true,
            'data' => [
                'module' => $module,
                'features' => $features,
                'module_info' => $availableModules[$module],
            ]
        ]);
    }

    /**
     * Check if tenant can access a specific feature
     */
    public function canAccessFeature(Request $request, string $feature): JsonResponse
    {
        $tenant = app('tenant');
        $canAccess = ModuleService::canAccessFeature($tenant, $feature);

        return response()->json([
            'success' => true,
            'data' => [
                'feature' => $feature,
                'can_access' => $canAccess,
            ]
        ]);
    }

    /**
     * Get modules by category
     */
    public function categories(): JsonResponse
    {
        $categories = ModuleService::getModulesByCategory();
        $availableModules = ModuleService::getAvailableModules();
        
        $result = [];
        foreach ($categories as $category => $moduleKeys) {
            $result[$category] = [];
            foreach ($moduleKeys as $moduleKey) {
                if (isset($availableModules[$moduleKey])) {
                    $result[$category][$moduleKey] = $availableModules[$moduleKey];
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Get recommended modules for tenant type
     */
    public function recommendations(Request $request): JsonResponse
    {
        $tenantType = $request->get('type', 'standard');
        $recommendedModules = ModuleService::getRecommendedModules($tenantType);
        $availableModules = ModuleService::getAvailableModules();
        
        $modules = [];
        foreach ($recommendedModules as $moduleKey) {
            if (isset($availableModules[$moduleKey])) {
                $modules[$moduleKey] = $availableModules[$moduleKey];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tenant_type' => $tenantType,
                'recommended_modules' => $modules,
                'estimated_monthly_cost' => array_sum(array_column($modules, 'price')),
            ]
        ]);
    }

    /**
     * Get tenant type based on active modules
     */
    private function getTenantType($tenant): string
    {
        $activeModules = ModuleService::getActiveModules($tenant);
        $activeCount = count($activeModules);

        if ($activeCount <= 1) {
            return 'basic';
        } elseif ($activeCount <= 3) {
            return 'standard';
        } elseif ($activeCount <= 5) {
            return 'premium';
        } else {
            return 'enterprise';
        }
    }
}