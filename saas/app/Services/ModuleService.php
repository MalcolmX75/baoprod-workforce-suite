<?php

namespace BaoProd\Workforce\Services;

use BaoProd\Workforce\Models\Tenant;

class ModuleService
{
    /**
     * Available modules configuration
     */
    public static function getAvailableModules(): array
    {
        return [
            'core' => [
                'name' => 'Job Board Core',
                'required' => true,
                'price' => 0,
                'description' => 'Gestion offres, candidatures, profils',
                'features' => [
                    'Job posting',
                    'Candidate management',
                    'Application tracking',
                    'Basic reporting',
                ]
            ],
            'contrats' => [
                'name' => 'Contrats & Signature',
                'required' => false,
                'price' => 50,
                'description' => 'Génération contrats, signature électronique',
                'features' => [
                    'Contract generation',
                    'Electronic signature',
                    'Contract templates',
                    'Legal compliance',
                ]
            ],
            'timesheets' => [
                'name' => 'Timesheets & Pointage',
                'required' => false,
                'price' => 80,
                'description' => 'Pointage mobile, calcul heures, validation',
                'features' => [
                    'Mobile time tracking',
                    'GPS location',
                    'Overtime calculation',
                    'Approval workflow',
                ]
            ],
            'paie' => [
                'name' => 'Paie & Facturation',
                'required' => false,
                'price' => 120,
                'description' => 'Calcul salaires, bulletins, facturation',
                'features' => [
                    'Payroll calculation',
                    'Payslip generation',
                    'Client invoicing',
                    'Tax compliance',
                ]
            ],
            'absences' => [
                'name' => 'Absences & Congés',
                'required' => false,
                'price' => 40,
                'description' => 'Demandes congés, validation, soldes',
                'features' => [
                    'Leave requests',
                    'Approval workflow',
                    'Leave balance tracking',
                    'Calendar integration',
                ]
            ],
            'reporting' => [
                'name' => 'Reporting & BI',
                'required' => false,
                'price' => 60,
                'description' => 'Tableaux de bord, KPIs, exports',
                'features' => [
                    'Advanced dashboards',
                    'KPI tracking',
                    'Data exports',
                    'Analytics',
                ]
            ],
            'messagerie' => [
                'name' => 'Messagerie & Notifications',
                'required' => false,
                'price' => 30,
                'description' => 'Chat interne, notifications, templates',
                'features' => [
                    'Internal messaging',
                    'Push notifications',
                    'Email templates',
                    'Communication history',
                ]
            ]
        ];
    }

    /**
     * Check if a module is active for a tenant
     */
    public static function isActive(Tenant $tenant, string $module): bool
    {
        if ($module === 'core') {
            return true; // Core is always active
        }

        $activeModules = $tenant->getActiveModules();
        return in_array($module, $activeModules);
    }

    /**
     * Get active modules for a tenant
     */
    public static function getActiveModules(Tenant $tenant): array
    {
        return $tenant->getActiveModules();
    }

    /**
     * Get inactive modules for a tenant
     */
    public static function getInactiveModules(Tenant $tenant): array
    {
        $allModules = array_keys(self::getAvailableModules());
        $activeModules = $tenant->getActiveModules();
        
        return array_diff($allModules, $activeModules);
    }

    /**
     * Calculate total monthly cost for a tenant
     */
    public static function calculateMonthlyCost(Tenant $tenant): float
    {
        $activeModules = $tenant->getActiveModules();
        $availableModules = self::getAvailableModules();
        
        $totalCost = 0;
        foreach ($activeModules as $module) {
            if (isset($availableModules[$module])) {
                $totalCost += $availableModules[$module]['price'];
            }
        }
        
        return $totalCost;
    }

    /**
     * Activate a module for a tenant
     */
    public static function activateModule(Tenant $tenant, string $module): bool
    {
        $availableModules = self::getAvailableModules();
        
        if (!isset($availableModules[$module])) {
            return false;
        }

        $activeModules = $tenant->getActiveModules();
        
        if (!in_array($module, $activeModules)) {
            $activeModules[] = $module;
            $tenant->update(['modules' => $activeModules]);
        }
        
        return true;
    }

    /**
     * Deactivate a module for a tenant
     */
    public static function deactivateModule(Tenant $tenant, string $module): bool
    {
        if ($module === 'core') {
            return false; // Cannot deactivate core module
        }

        $activeModules = $tenant->getActiveModules();
        $activeModules = array_filter($activeModules, fn($m) => $m !== $module);
        
        $tenant->update(['modules' => array_values($activeModules)]);
        
        return true;
    }

    /**
     * Get module features
     */
    public static function getModuleFeatures(string $module): array
    {
        $availableModules = self::getAvailableModules();
        
        return $availableModules[$module]['features'] ?? [];
    }

    /**
     * Check if tenant can access a feature
     */
    public static function canAccessFeature(Tenant $tenant, string $feature): bool
    {
        $activeModules = $tenant->getActiveModules();
        $availableModules = self::getAvailableModules();
        
        foreach ($activeModules as $module) {
            if (isset($availableModules[$module]['features'])) {
                if (in_array($feature, $availableModules[$module]['features'])) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Get modules by category
     */
    public static function getModulesByCategory(): array
    {
        return [
            'core' => ['core'],
            'hr' => ['contrats', 'timesheets', 'paie', 'absences'],
            'communication' => ['messagerie'],
            'analytics' => ['reporting'],
        ];
    }

    /**
     * Get recommended modules for a tenant type
     */
    public static function getRecommendedModules(string $tenantType = 'standard'): array
    {
        $recommendations = [
            'basic' => ['core'],
            'standard' => ['core', 'contrats', 'timesheets'],
            'premium' => ['core', 'contrats', 'timesheets', 'paie', 'absences'],
            'enterprise' => ['core', 'contrats', 'timesheets', 'paie', 'absences', 'reporting', 'messagerie'],
        ];

        return $recommendations[$tenantType] ?? $recommendations['standard'];
    }
}