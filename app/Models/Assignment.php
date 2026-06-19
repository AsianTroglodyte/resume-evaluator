<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;

class Assignment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'module_id',
        'created_by_user_id',
        'title',
        'description',
        'due_at',
        'assignee_scope',
        'Job_listing_source',
        'module_job_listing_scope',
        'Module',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'allow_resubmission' => 'boolean',
            'assignee_scope' => AssigneeScope::class,
            'job_listing_source' => JobListingSource::class,
            'module_job_listing_scope' => ModuleJobListingScope::class
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
