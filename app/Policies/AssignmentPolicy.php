<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Module;
use App\Models\User;

class AssignmentPolicy
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
    public function view(User $user, Assignment $assignment): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($assignment->module)
            || $user->isGivenAssignment($assignment);
    }

    public function submit(User $user, Assignment $assignment): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($assignment->module)
            || $user->isGivenAssignment($assignment);
    }

    /**
     * Only students given the assignment hold claims; instructors never consume capacity.
     */
    public function claim(User $user, Assignment $assignment): bool
    {
        return $assignment->usesModuleListings()
            && $assignment->module->assignableMembers()->whereKey($user->id)->exists()
            && $user->isGivenAssignment($assignment);
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
    public function update(User $user, Assignment $assignment): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($assignment->module);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Assignment $assignment): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($assignment->module);
    }

    public function seeAllAssignmentDetails(User $user, Assignment $assignment): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($assignment->module);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Assignment $assignment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Assignment $assignment): bool
    {
        return false;
    }
}
