<?php

namespace App\Policies;

use App\Enums\AssigneeScope;
use App\Models\JobListing;
use App\Models\Module;
use App\Models\User;

class JobListingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobListing $jobListing): bool
    {
        if ($user->isGlobalAdmin()) {
            return true;
        }

        $module = $jobListing->module;

        if ($module !== null && $user->isInstructorInModule($module)) {
            return true;
        }

        return $this->visibleThroughUserAssignments($user, $jobListing);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Module $module): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($module);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobListing $jobListing): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($jobListing->module);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobListing $jobListing): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($jobListing->module);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JobListing $jobListing): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobListing $jobListing): bool
    {
        return $user->isGlobalAdmin();
    }

    private function visibleThroughUserAssignments(User $user, JobListing $jobListing): bool
    {

        $module = $jobListing->module;

        if ($module === null || !$user->isInModule($module)) {
            return false;
        }
        return $jobListing->assignments()
            ->where(function ($query) use ($user) {
                $query->where('assignee_scope', AssigneeScope::Everyone)
                    ->orWhereHas('assignees', fn ($q) => $q->whereKey($user->id));
            })
            ->exists();
    }
}
