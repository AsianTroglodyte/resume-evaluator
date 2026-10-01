<?php

namespace App\Models;

use App\Enums\JobListingSource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobListing extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'module_id',
        'name',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'job_listing_source' => JobListingSource::class,
        ];
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function assignments(): BelongsToMany
    {
        return $this->belongsToMany(Assignment::class, 'assignment_allowed_job_listings');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(JobListingClaim::class);
    }

    public function isFull(): bool
    {
        return $this->capacity !== null && $this->claims_count >= $this->capacity;
    }
}
