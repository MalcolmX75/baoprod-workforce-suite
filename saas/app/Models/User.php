<?php

namespace BaoProd\Workforce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'avatar',
        'type',
        'profile_data',
        'preferences',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'profile_data' => 'array',
        'preferences' => 'array',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the tenant that owns the user
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get jobs posted by this user (if employer)
     */
    public function postedJobs(): HasMany
    {
        return $this->hasMany(Job::class, 'employer_id');
    }

    /**
     * Get applications submitted by this user (if candidate)
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'candidate_id');
    }

    /**
     * Get applications reviewed by this user (if employer/admin)
     */
    public function reviewedApplications(): HasMany
    {
        return $this->hasMany(Application::class, 'reviewed_by');
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Check if user is a candidate
     */
    public function isCandidate(): bool
    {
        return $this->type === 'candidate';
    }

    /**
     * Check if user is an employer
     */
    public function isEmployer(): bool
    {
        return $this->type === 'employer';
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    /**
     * Check if user is a manager
     */
    public function isManager(): bool
    {
        return $this->type === 'manager';
    }

    /**
     * Get profile data for candidate
     */
    public function getCandidateProfile(): ?array
    {
        if (!$this->isCandidate()) {
            return null;
        }

        return $this->profile_data ?? [
            'skills' => [],
            'experience' => [],
            'education' => [],
            'languages' => [],
            'availability' => 'immediate',
            'expected_salary' => null,
            'cv_url' => null,
            'portfolio_url' => null,
        ];
    }

    /**
     * Get profile data for employer
     */
    public function getEmployerProfile(): ?array
    {
        if (!$this->isEmployer()) {
            return null;
        }

        return $this->profile_data ?? [
            'company_name' => null,
            'company_size' => null,
            'industry' => null,
            'website' => null,
            'description' => null,
            'logo_url' => null,
        ];
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for users by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for users by tenant
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}