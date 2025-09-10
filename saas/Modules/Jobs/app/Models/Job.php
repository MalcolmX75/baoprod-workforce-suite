<?php

namespace Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use BaoProd\Workforce\Models\User;

class Job extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'employer_id',
        'job_category_id',
        'title',
        'description',
        'requirements',
        'location',
        'latitude',
        'longitude',
        'type',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_period',
        'start_date',
        'positions_available',
        'benefits',
        'skills_required',
        'experience_required',
        'education_level',
        'is_remote',
        'is_featured',
        'status',
        'published_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'start_date' => 'date',
        'benefits' => 'array',
        'skills_required' => 'array',
        'is_remote' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the employer that owns the job.
     */
    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    /**
     * Get the category for the job.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    /**
     * Get the applications for the job.
     */
    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
