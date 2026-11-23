<?php

namespace App\Models;

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Enums\ModuleMembershipStatus;
use App\Enums\RoleInModule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Assignment extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'module_id',
        'created_by_user_id',
        'title',
        'description',
        'due_date',
        'assignee_scope',
        'module_group_id',
        'job_listing_source',
        'module_job_listing_scope',
        'allow_resubmission',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
            'allow_resubmission' => 'boolean',
            'assignee_scope' => AssigneeScope::class,
            'job_listing_source' => JobListingSource::class,
            'module_job_listing_scope' => ModuleJobListingScope::class,
        ];
    }

    public function assignees(): BelongsToMany
    {
        return $this->assigneesWithMembershipStatus('active');
    }

    public function allAssignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assignment_assignees');
    }

    public function removedAssignees(): BelongsToMany
    {
        return $this->assigneesWithMembershipStatus('removed');
    }

    public function jobListings(): BelongsToMany
    {
        return $this->belongsToMany(JobListing::class, 'assignment_allowed_job_listings')
            ->withPivot('capacity');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(JobListingClaim::class);
    }

    public function claimFor(User $user): HasOne
    {
        return $this->hasOne(JobListingClaim::class)
            ->where('user_id', $user->id);
    }

    public function usesModuleListings(): bool
    {
        return $this->job_listing_source !== JobListingSource::External;
    }

    /**
     * Module-only assignments take their JD from the student's claim; "both" lets
     * students paste an external JD instead of claiming.
     */
    public function requiresClaim(): bool
    {
        return $this->job_listing_source === JobListingSource::Module;
    }

    /**
     * Listings students may claim, each with `capacity` (null = unlimited) and `claims_count`.
     * "All module listings" has no attachment rows, so those listings are uncapped.
     *
     * @return Builder<JobListing>
     */
    public function claimableJobListings(): Builder
    {
        $query = JobListing::query()
            ->where('job_listings.module_id', $this->module_id)
            ->unless($this->usesModuleListings(), fn (Builder $none) => $none->whereRaw('1 = 0'));

        if ($this->module_job_listing_scope === ModuleJobListingScope::Selected) {
            $query->join('assignment_allowed_job_listings as allowed', function ($join): void {
                $join->on('allowed.job_listing_id', '=', 'job_listings.id')
                    ->where('allowed.assignment_id', $this->id);
            })->select(['job_listings.*', 'allowed.capacity']);
        } else {
            $query->select('job_listings.*')->selectRaw('null as capacity');
        }

        return $query
            ->withCount(['claims' => fn (Builder $claims) => $claims->where('assignment_id', $this->id)])
            ->orderBy('job_listings.name');
    }

    public function assignmentAssignees(): HasMany
    {
        return $this->hasMany(AssignmentAssignees::class);
    }

    public function assignmentAllowedJobListings(): HasMany
    {
        return $this->hasMany(AssignmentAllowedJobListings::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(ModuleGroup::class, 'module_group_id');
    }

    /**
     * Assignments given to the user as an active module member: everyone-scoped,
     * group-scoped to the user's current group, or selected with the user as assignee.
     *
     * @param  Builder<Assignment>  $query
     */
    public function scopeGivenTo(Builder $query, User $user): void
    {
        $activeMembershipOf = fn ($membership) => $membership->selectRaw('1')
            ->from('module_memberships')
            ->whereColumn('module_memberships.module_id', 'assignments.module_id')
            ->where('module_memberships.user_id', $user->id)
            ->where('module_memberships.status', ModuleMembershipStatus::Active->value);

        $query->whereExists($activeMembershipOf)
            ->where(function (Builder $scope) use ($user, $activeMembershipOf): void {
                $scope->where('assignee_scope', AssigneeScope::Everyone->value)
                    ->orWhere(fn (Builder $group) => $group
                        ->where('assignee_scope', AssigneeScope::Group->value)
                        ->whereExists(fn ($membership) => $activeMembershipOf($membership)
                            ->whereColumn('module_memberships.module_group_id', 'assignments.module_group_id')))
                    ->orWhere(fn (Builder $selected) => $selected
                        ->where('assignee_scope', AssigneeScope::Selected->value)
                        ->whereHas('allAssignees', fn (Builder $assignee) => $assignee->whereKey($user->id)));
            });
    }

    /**
     * Students this assignment applies to, for instructor rosters.
     *
     * @return BelongsToMany<User, Module>|BelongsToMany<User, ModuleGroup>|BelongsToMany<User, Assignment>
     */
    public function roster(): BelongsToMany
    {
        return match ($this->assignee_scope) {
            AssigneeScope::Everyone => $this->module->assignableMembers(),
            AssigneeScope::Group => $this->group->members()
                ->wherePivot('role_in_module', RoleInModule::Student->value),
            AssigneeScope::Selected => $this->assignees(),
        };
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function allEvaluations(): HasManyThrough
    {
        return $this->hasManyThrough(Evaluation::class, Submission::class);
    }

    public function submissionFor(User $user): HasOne
    {
        return $this->hasOne(Submission::class)
            ->where('user_id', $user->id);
    }

    public function evaluationFor(User $user): HasOneThrough
    {
        return $this->hasOneThrough(Evaluation::class, Submission::class)
            ->where('user_id', $user->id);
    }

    public function isPastDue(): bool
    {
        return $this->due_date?->isPast() ?? false;
    }

    // public function (User $user): HasOne
    // {
    //     return $this->hasOne(Submission::class)
    //         ->where('user_id', $user->id);
    // }

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
