<?php

namespace App\Policies;

use App\Enums\AssigneeScope;
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
        $isAssigned = 
            $user->isInModule($assignment->module)
            && ($assignment->assignee_scope === AssigneeScope::Everyone 
                || $assignment->assignees()->whereKey($user->id)->exists());

        // dd($assignment->module);
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($assignment->module)
            || $isAssigned;
    }

    public function submit(User $user, Assignment $assignment): bool
    {
        $isAssigned = 
            $user->isInModule($assignment->module)
            && ($assignment->assignee_scope === AssigneeScope::Everyone 
                || $assignment->assignees()->whereKey($user->id)->exists());

        return  $user->isGlobalAdmin()
                || $user->isInstructorInModule($assignment->module)
                || $isAssigned;
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
