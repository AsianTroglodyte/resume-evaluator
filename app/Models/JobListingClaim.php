<?php

namespace App\Models;

use Database\Factories\JobListingClaimFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobListingClaim extends Model
{
    /** @use HasFactory<JobListingClaimFactory> */
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'job_listing_id',
        'user_id',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
