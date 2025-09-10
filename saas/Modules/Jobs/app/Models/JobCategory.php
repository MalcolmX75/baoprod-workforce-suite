<?php

namespace Modules\Jobs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
    ];

    /**
     * Get the jobs for the category.
     */
    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }
}
