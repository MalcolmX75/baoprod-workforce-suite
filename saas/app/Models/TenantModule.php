<?php

namespace BaoProd\Workforce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenantModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'module_code',
        'is_active',
        'configuration',
        'activated_at',
        'expires_at',
        'bundle_code',
        'price_monthly',
        'price_yearly',
        'max_users',
        'features',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'configuration' => 'array',
        'features' => 'array',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns this module
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if module is active and not expired
     */
    public function isActiveAndValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if a specific feature is enabled
     */
    public function hasFeature(string $feature): bool
    {
        if (!$this->isActiveAndValid()) {
            return false;
        }

        $features = $this->features ?? [];
        return in_array($feature, $features) || in_array('*', $features);
    }

    /**
     * Get module configuration value
     */
    public function getConfig(string $key, $default = null)
    {
        $config = $this->configuration ?? [];
        return data_get($config, $key, $default);
    }

    /**
     * Set module configuration value
     */
    public function setConfig(string $key, $value): void
    {
        $config = $this->configuration ?? [];
        data_set($config, $key, $value);
        $this->configuration = $config;
        $this->save();
    }

    /**
     * Scope for active modules
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for modules by tenant
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope for specific module
     */
    public function scopeForModule($query, string $moduleCode)
    {
        return $query->where('module_code', $moduleCode);
    }
}