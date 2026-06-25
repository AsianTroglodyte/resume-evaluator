<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

class ModulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isGlobalAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Module $module): bool
    {
        return $user->isInModule($module) || $user->isGlobalAdmin();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isGlobalAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Module $module): bool
    {
        return $user->isInstructorInModule($module) || $user->isGlobalAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Module $module): bool
    {
        return $user->isGlobalAdmin();
    }

    /** 
     * 
    */
    public function viewMembers(User $user, Module $module)
    {
        return $user->isInstructorInModule($module) || $user->isGlobalAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function removeUsers(User $user, Module $module)
    {
        return $user->isInstructorInModule($module) || $user->isGlobalAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Module $module): bool
    {
        return $user->isGlobalAdmin();
    }
}
