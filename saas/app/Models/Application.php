<?php

namespace BaoProd\Workforce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'job_id',
        'candidate_id',
        'status',
        'cover_letter',
        'documents',
        'expected_salary',
        'available_start_date',
        'notes',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'documents' => 'array',
        'available_start_date' => 'date',
        'reviewed_at' => 'datetime',
        'expected_salary' => 'decimal:2',
    ];

    /**
     * Get the tenant that owns the application
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the job for this application
     */
    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    /**
     * Get the candidate who submitted this application
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    /**
     * Get the user who reviewed this application
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get status in French
     */
    public function getStatusInFrenchAttribute(): string
    {
        $statuses = [
            'pending' => 'En attente',
            'reviewed' => 'Examinée',
            'shortlisted' => 'Présélectionnée',
            'interviewed' => 'Entretien passé',
            'accepted' => 'Acceptée',
            'rejected' => 'Rejetée',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Check if application is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if application is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if application is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if application has been reviewed
     */
    public function isReviewed(): bool
    {
        return $this->status !== 'pending' && $this->reviewed_at;
    }

    /**
     * Get formatted expected salary
     */
    public function getFormattedExpectedSalaryAttribute(): ?string
    {
        if (!$this->expected_salary) {
            return null;
        }

        return number_format($this->expected_salary, 0, ',', ' ') . ' XOF';
    }

    /**
     * Get documents count
     */
    public function getDocumentsCountAttribute(): int
    {
        return count($this->documents ?? []);
    }

    /**
     * Scope for applications by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending applications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for reviewed applications
     */
    public function scopeReviewed($query)
    {
        return $query->where('status', '!=', 'pending')
                    ->whereNotNull('reviewed_at');
    }

    /**
     * Scope for applications by candidate
     */
    public function scopeByCandidate($query, int $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    /**
     * Scope for applications by job
     */
    public function scopeForJob($query, int $jobId)
    {
        return $query->where('job_id', $jobId);
    }

    /**
     * Scope for applications by tenant
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }
}