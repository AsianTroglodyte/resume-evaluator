<?php

namespace App\Models;

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'job_listing_source',
        'module_job_listing_scope',
        'allow_resubmission',
        'Module',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'allow_resubmission' => 'boolean',
            'assignee_scope' => AssigneeScope::class,
            'job_listing_source' => JobListingSource::class,
            'module_job_listing_scope' => ModuleJobListingScope::class,
        ];
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignment_assignees');
    }

    public function activeAssignees(): BelongsToMany
    {
        return $this->assigneesWithMembershipStatus('active');
    }

    public function removedAssignees(): BelongsToMany
    {
        return $this->assigneesWithMembershipStatus('removed');
    }

    public function jobListings(): BelongsToMany
    {
        return $this->belongsToMany(JobListing::class, 'assignment_allowed_job_listings');
    }

    public function assignmentAssignees(): HasMany
    {
        return $this->hasMany(AssignmentAssignees::class);
    }

    public function assignmentAllowedJobListings(): HasMany
    {
        return $this->hasMany(AssignmentAllowedJobListings::class);

    }

    private function assigneesWithMembershipStatus(string $status): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignment_assignees')
            ->whereExists(function ($query) use ($status) {
                $query->selectRaw('1')
                    ->from('module_memberships')
                    ->join('assignments', 'assignments.module_id', '=', 'module_memberships.module_id')
                    ->whereColumn('assignments.id', 'assignment_assignees.assignment_id')
                    ->whereColumn('module_memberships.user_id', 'users.id')
                    ->where('module_memberships.status', $status);
            });
    }
}
