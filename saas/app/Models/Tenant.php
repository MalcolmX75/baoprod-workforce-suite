<?php

namespace BaoProd\Workforce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'domain',
        'subdomain',
        'country_code',
        'currency',
        'language',
        'settings',
        'modules',
        'is_active',
        'trial_ends_at',
        'subscription_ends_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'modules' => 'array',
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
    ];

    /**
     * Get all users for this tenant
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all jobs for this tenant
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    /**
     * Get all applications for this tenant
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get all contrats for this tenant
     */
    public function contrats(): HasMany
    {
        return $this->hasMany(Contrat::class);
    }

    /**
     * Get all timesheets for this tenant
     */
    public function timesheets(): HasMany
    {
        return $this->hasMany(Timesheet::class);
    }

    /**
     * Get all paie records for this tenant
     */
    public function paie(): HasMany
    {
        return $this->hasMany(Paie::class);
    }

    /**
     * Check if a module is active for this tenant
     */
    public function hasModule(string $module): bool
    {
        $modules = $this->modules ?? [];
        return in_array($module, $modules);
    }

    /**
     * Get active modules for this tenant
     */
    public function getActiveModules(): array
    {
        return $this->modules ?? ['core']; // Core is always active
    }

    /**
     * Check if tenant is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if tenant subscription is active
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isFuture();
    }

    /**
     * Get CEMAC configuration for this tenant's country
     */
    public function getCemacConfig(): array
    {
        $configs = [
            'GA' => [ // Gabon
                'social_charges' => ['employer' => 0.215, 'employee' => 0.065, 'total' => 0.28],
                'overtime_rates' => ['daily' => 1.25, 'sunday' => 1.50, 'holiday' => 1.50, 'night' => 1.30],
                'minimum_wage' => 80000,
                'currency' => 'XOF',
                'working_hours' => 40,
            ],
            'CM' => [ // Cameroun
                'social_charges' => ['employer' => 0.155, 'employee' => 0.045, 'total' => 0.20],
                'overtime_rates' => ['daily' => 1.25, 'sunday' => 1.50, 'holiday' => 1.50, 'night' => 1.30],
                'minimum_wage' => 36270,
                'currency' => 'XOF',
                'working_hours' => 40,
            ],
            'TD' => [ // Tchad
                'social_charges' => ['employer' => 0.185, 'employee' => 0.065, 'total' => 0.25],
                'overtime_rates' => ['daily' => 1.25, 'sunday' => 1.50, 'holiday' => 1.50, 'night' => 1.30],
                'minimum_wage' => 60000,
                'currency' => 'XAF',
                'working_hours' => 39,
            ],
            'CF' => [ // RCA
                'social_charges' => ['employer' => 0.20, 'employee' => 0.05, 'total' => 0.25],
                'overtime_rates' => ['daily' => 1.25, 'sunday' => 1.50, 'holiday' => 1.50, 'night' => 1.30],
                'minimum_wage' => 35000,
                'currency' => 'XAF',
                'working_hours' => 40,
            ],
            'GQ' => [ // Guinée Équatoriale
                'social_charges' => ['employer' => 0.22, 'employee' => 0.045, 'total' => 0.265],
                'overtime_rates' => ['daily' => 1.25, 'sunday' => 1.50, 'holiday' => 1.50, 'night' => 1.30],
                'minimum_wage' => 150000,
                'currency' => 'XAF',
                'working_hours' => 40,
            ],
            'CG' => [ // Congo
                'social_charges' => ['employer' => 0.195, 'employee' => 0.055, 'total' => 0.25],
                'overtime_rates' => ['daily' => 1.25, 'sunday' => 1.50, 'holiday' => 1.50, 'night' => 1.30],
                'minimum_wage' => 90000,
                'currency' => 'XAF',
                'working_hours' => 40,
            ],
        ];

        return $configs[$this->country_code] ?? $configs['GA'];
    }
}