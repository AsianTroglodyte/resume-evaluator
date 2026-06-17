<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Assignment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'module_id',
        'created_by_user_id',
        'title',
        'description',
        'status',
        'due_at',
        'assignment_scope',
        'job_listing_rule',
        'allow_resubmission',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'allow_resubmission' => 'boolean',
        ];
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignment_assignees');
    }

    public function jobListings(): BelongsToMany
    {
        return $this->belongsToMany(JobListing::class, 'assignment_allowed_job_listings');
    }
}
