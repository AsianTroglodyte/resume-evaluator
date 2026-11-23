<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\ModuleGroup;
use App\Models\User;

class ModuleGroupPolicy
{
    /**
     * Determine whether the user can view the module's groups.
     */
    public function viewAny(User $user, Module $module): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($module);
    }

    /**
     * Determine whether the user can view the group and its members.
     */
    public function view(User $user, ModuleGroup $moduleGroup): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($moduleGroup->module);
    }

    /**
     * Determine whether the user can create groups in the module.
     */
    public function create(User $user, Module $module): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($module);
    }

    /**
     * Determine whether the user can update the group.
     */
    public function update(User $user, ModuleGroup $moduleGroup): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($moduleGroup->module);
    }

    /**
     * Determine whether the user can delete the group.
     */
    public function delete(User $user, ModuleGroup $moduleGroup): bool
    {
        return $user->isGlobalAdmin()
            || $user->isInstructorInModule($moduleGroup->module);
    }
}
