<?php

namespace BaoProd\Workforce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'employer_id',
        'title',
        'description',
        'requirements',
        'location',
        'latitude',
        'longitude',
        'type',
        'status',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_period',
        'start_date',
        'end_date',
        'positions_available',
        'benefits',
        'skills_required',
        'experience_required',
        'education_level',
        'is_remote',
        'is_featured',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'benefits' => 'array',
        'skills_required' => 'array',
        'is_remote' => 'boolean',
        'is_featured' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'start_date' => 'date',
        'end_date' => 'date',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the job
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the employer who posted this job
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    /**
     * Get all applications for this job
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * Get formatted salary range
     */
    public function getSalaryRangeAttribute(): string
    {
        if (!$this->salary_min && !$this->salary_max) {
            return 'Salaire non spécifié';
        }

        if ($this->salary_min && $this->salary_max) {
            return number_format($this->salary_min, 0, ',', ' ') . ' - ' . 
                   number_format($this->salary_max, 0, ',', ' ') . ' ' . $this->salary_currency;
        }

        if ($this->salary_min) {
            return 'À partir de ' . number_format($this->salary_min, 0, ',', ' ') . ' ' . $this->salary_currency;
        }

        return 'Jusqu\'à ' . number_format($this->salary_max, 0, ',', ' ') . ' ' . $this->salary_currency;
    }

    /**
     * Get job type in French
     */
    public function getTypeInFrenchAttribute(): string
    {
        $types = [
            'full_time' => 'Temps plein',
            'part_time' => 'Temps partiel',
            'contract' => 'Contrat',
            'temporary' => 'Temporaire',
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Get job status in French
     */
    public function getStatusInFrenchAttribute(): string
    {
        $statuses = [
            'draft' => 'Brouillon',
            'published' => 'Publié',
            'closed' => 'Fermé',
            'filled' => 'Pourvu',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Check if job is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->published_at;
    }

    /**
     * Check if job is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if job is active (published and not expired)
     */
    public function isActive(): bool
    {
        return $this->isPublished() && !$this->isExpired();
    }

    /**
     * Get applications count
     */
    public function getApplicationsCountAttribute(): int
    {
        return $this->applications()->count();
    }

    /**
     * Scope for published jobs
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->whereNotNull('published_at');
    }

    /**
     * Scope for active jobs (published and not expired)
     */
    public function scopeActive($query)
    {
        return $query->published()
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Scope for featured jobs
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for jobs by type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for jobs by location
     */
    public function scopeInLocation($query, string $location)
    {
        return $query->where('location', 'like', '%' . $location . '%');
    }

    /**
     * Scope for jobs by salary range
     */
    public function scopeWithSalaryRange($query, float $minSalary = null, float $maxSalary = null)
    {
        if ($minSalary) {
            $query->where(function ($q) use ($minSalary) {
                $q->whereNull('salary_max')
                  ->orWhere('salary_max', '>=', $minSalary);
            });
        }

        if ($maxSalary) {
            $query->where(function ($q) use ($maxSalary) {
                $q->whereNull('salary_min')
                  ->orWhere('salary_min', '<=', $maxSalary);
            });
        }

        return $query;
    }

    /**
     * Scope for remote jobs
     */
    public function scopeRemote($query)
    {
        return $query->where('is_remote', true);
    }
}