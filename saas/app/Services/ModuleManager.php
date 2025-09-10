<?php

namespace BaoProd\Workforce\Services;

use BaoProd\Workforce\Models\TenantModule;
use BaoProd\Workforce\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Exception;

class ModuleManager
{
    /**
     * Available modules configuration
     */
    const AVAILABLE_MODULES = [
        'jobs' => [
            'name' => 'Module Emplois',
            'description' => 'Gestion des offres d\'emploi et du recrutement',
            'icon' => 'briefcase',
            'features' => ['job_posting', 'applications', 'recruitment_workflow', 'job_categories'],
            'dependencies' => [],
            'price_monthly' => 29.00,
            'price_yearly' => 290.00,
        ],
        'hr' => [
            'name' => 'Module RH',
            'description' => 'Gestion des employés et des contrats',
            'icon' => 'people',
            'features' => ['employee_management', 'contracts', 'documents', 'evaluations'],
            'dependencies' => [],
            'price_monthly' => 39.00,
            'price_yearly' => 390.00,
        ],
        'payroll' => [
            'name' => 'Module Paie',
            'description' => 'Calculs de paie et déclarations sociales',
            'icon' => 'currency-dollar',
            'features' => ['salary_calculation', 'payslips', 'social_declarations', 'tax_reports'],
            'dependencies' => ['hr', 'time'],
            'price_monthly' => 49.00,
            'price_yearly' => 490.00,
        ],
        'time' => [
            'name' => 'Module Temps',
            'description' => 'Pointage et gestion des présences',
            'icon' => 'clock',
            'features' => ['time_tracking', 'attendance', 'schedules', 'overtime'],
            'dependencies' => ['hr'],
            'price_monthly' => 19.00,
            'price_yearly' => 190.00,
        ],
        'leave' => [
            'name' => 'Module Congés',
            'description' => 'Gestion des congés et absences',
            'icon' => 'calendar-check',
            'features' => ['leave_requests', 'approval_workflow', 'leave_balance', 'calendar'],
            'dependencies' => ['hr'],
            'price_monthly' => 15.00,
            'price_yearly' => 150.00,
        ],
        'training' => [
            'name' => 'Module Formation',
            'description' => 'Plans de formation et certifications',
            'icon' => 'book',
            'features' => ['training_plans', 'sessions', 'certificates', 'skills_tracking'],
            'dependencies' => ['hr'],
            'price_monthly' => 25.00,
            'price_yearly' => 250.00,
        ],
        'projects' => [
            'name' => 'Module Projets',
            'description' => 'Gestion de projets et ressources',
            'icon' => 'diagram-project',
            'features' => ['project_management', 'resource_allocation', 'gantt', 'time_tracking'],
            'dependencies' => ['hr', 'time'],
            'price_monthly' => 35.00,
            'price_yearly' => 350.00,
        ],
        'crm' => [
            'name' => 'Module CRM',
            'description' => 'Gestion de la relation client',
            'icon' => 'person-heart',
            'features' => ['client_management', 'opportunities', 'interactions', 'pipeline'],
            'dependencies' => [],
            'price_monthly' => 29.00,
            'price_yearly' => 290.00,
        ],
        'billing' => [
            'name' => 'Module Facturation',
            'description' => 'Devis et facturation clients',
            'icon' => 'receipt',
            'features' => ['quotes', 'invoices', 'payments', 'reports'],
            'dependencies' => ['crm'],
            'price_monthly' => 39.00,
            'price_yearly' => 390.00,
        ],
    ];

    /**
     * Available bundles configuration
     */
    const AVAILABLE_BUNDLES = [
        'starter' => [
            'name' => 'Starter',
            'description' => 'Idéal pour les petites entreprises',
            'modules' => ['jobs', 'hr'],
            'max_users' => 10,
            'price_monthly' => 49.00,
            'price_yearly' => 490.00,
            'discount' => 25, // 25% de réduction vs prix individuels
        ],
        'professional' => [
            'name' => 'Professional',
            'description' => 'Pour les entreprises en croissance',
            'modules' => ['jobs', 'hr', 'payroll', 'time', 'leave'],
            'max_users' => 50,
            'price_monthly' => 129.00,
            'price_yearly' => 1290.00,
            'discount' => 35,
        ],
        'enterprise' => [
            'name' => 'Enterprise',
            'description' => 'Solution complète pour grandes entreprises',
            'modules' => ['jobs', 'hr', 'payroll', 'time', 'leave', 'training', 'projects', 'crm', 'billing'],
            'max_users' => null, // Illimité
            'price_monthly' => 249.00,
            'price_yearly' => 2490.00,
            'discount' => 50,
        ],
    ];

    /**
     * Check if a module is active for a tenant
     */
    public function isModuleActive(string $moduleCode, int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? $this->getCurrentTenantId();
        
        if (!$tenantId) {
            return false;
        }

        $cacheKey = "module_active_{$tenantId}_{$moduleCode}";
        
        return Cache::remember($cacheKey, 300, function () use ($moduleCode, $tenantId) {
            return TenantModule::forTenant($tenantId)
                             ->forModule($moduleCode)
                             ->active()
                             ->exists();
        });
    }

    /**
     * Get all active modules for a tenant
     */
    public function getActiveModules(int $tenantId = null): Collection
    {
        $tenantId = $tenantId ?? $this->getCurrentTenantId();
        
        $cacheKey = "active_modules_{$tenantId}";
        
        return Cache::remember($cacheKey, 300, function () use ($tenantId) {
            return TenantModule::forTenant($tenantId)
                             ->active()
                             ->pluck('module_code');
        });
    }

    /**
     * Activate a module for a tenant
     */
    public function activateModule(string $moduleCode, int $tenantId, array $options = []): bool
    {
        if (!$this->isValidModule($moduleCode)) {
            throw new Exception("Module '{$moduleCode}' is not valid.");
        }

        // Check dependencies
        $dependencies = self::AVAILABLE_MODULES[$moduleCode]['dependencies'] ?? [];
        foreach ($dependencies as $dependency) {
            if (!$this->isModuleActive($dependency, $tenantId)) {
                throw new Exception("Module '{$moduleCode}' requires '{$dependency}' to be active.");
            }
        }

        $moduleConfig = self::AVAILABLE_MODULES[$moduleCode];
        
        $tenantModule = TenantModule::updateOrCreate([
            'tenant_id' => $tenantId,
            'module_code' => $moduleCode,
        ], [
            'is_active' => true,
            'activated_at' => now(),
            'expires_at' => $options['expires_at'] ?? null,
            'bundle_code' => $options['bundle_code'] ?? null,
            'price_monthly' => $options['price_monthly'] ?? $moduleConfig['price_monthly'],
            'price_yearly' => $options['price_yearly'] ?? $moduleConfig['price_yearly'],
            'max_users' => $options['max_users'] ?? null,
            'features' => $options['features'] ?? $moduleConfig['features'],
            'configuration' => $options['configuration'] ?? [],
        ]);

        $this->clearCache($tenantId);
        
        return true;
    }

    /**
     * Deactivate a module for a tenant
     */
    public function deactivateModule(string $moduleCode, int $tenantId): bool
    {
        // Check if other modules depend on this one
        $activeModules = $this->getActiveModules($tenantId);
        foreach ($activeModules as $activeModule) {
            if ($activeModule === $moduleCode) continue;
            
            $dependencies = self::AVAILABLE_MODULES[$activeModule]['dependencies'] ?? [];
            if (in_array($moduleCode, $dependencies)) {
                throw new Exception("Cannot deactivate '{$moduleCode}' because '{$activeModule}' depends on it.");
            }
        }

        TenantModule::forTenant($tenantId)
                   ->forModule($moduleCode)
                   ->update(['is_active' => false]);

        $this->clearCache($tenantId);
        
        return true;
    }

    /**
     * Activate a bundle for a tenant
     */
    public function activateBundle(string $bundleCode, int $tenantId, array $options = []): bool
    {
        if (!$this->isValidBundle($bundleCode)) {
            throw new Exception("Bundle '{$bundleCode}' is not valid.");
        }

        $bundleConfig = self::AVAILABLE_BUNDLES[$bundleCode];
        $modules = $bundleConfig['modules'];

        // Deactivate all current modules first
        TenantModule::forTenant($tenantId)->update(['is_active' => false]);

        // Activate bundle modules
        foreach ($modules as $moduleCode) {
            $this->activateModule($moduleCode, $tenantId, [
                'bundle_code' => $bundleCode,
                'max_users' => $bundleConfig['max_users'],
                'price_monthly' => $bundleConfig['price_monthly'] / count($modules),
                'price_yearly' => $bundleConfig['price_yearly'] / count($modules),
                'expires_at' => $options['expires_at'] ?? null,
            ]);
        }

        return true;
    }

    /**
     * Get module configuration
     */
    public function getModuleConfig(string $moduleCode, int $tenantId = null): ?TenantModule
    {
        $tenantId = $tenantId ?? $this->getCurrentTenantId();
        
        return TenantModule::forTenant($tenantId)
                          ->forModule($moduleCode)
                          ->first();
    }

    /**
     * Check if module has specific feature
     */
    public function hasFeature(string $moduleCode, string $feature, int $tenantId = null): bool
    {
        $moduleConfig = $this->getModuleConfig($moduleCode, $tenantId);
        
        return $moduleConfig && $moduleConfig->hasFeature($feature);
    }

    /**
     * Get available modules
     */
    public function getAvailableModules(): array
    {
        return self::AVAILABLE_MODULES;
    }

    /**
     * Get available bundles
     */
    public function getAvailableBundles(): array
    {
        return self::AVAILABLE_BUNDLES;
    }

    /**
     * Check if module code is valid
     */
    private function isValidModule(string $moduleCode): bool
    {
        return array_key_exists($moduleCode, self::AVAILABLE_MODULES);
    }

    /**
     * Check if bundle code is valid
     */
    private function isValidBundle(string $bundleCode): bool
    {
        return array_key_exists($bundleCode, self::AVAILABLE_BUNDLES);
    }

    /**
     * Clear cache for tenant
     */
    private function clearCache(int $tenantId): void
    {
        Cache::forget("active_modules_{$tenantId}");
        
        foreach (self::AVAILABLE_MODULES as $moduleCode => $config) {
            Cache::forget("module_active_{$tenantId}_{$moduleCode}");
        }
    }

    /**
     * Get current tenant ID
     */
    private function getCurrentTenantId(): ?int
    {
        // For now, return 1 as default. In future, get from session/auth
        return auth()->user()->tenant_id ?? 1;
    }
}