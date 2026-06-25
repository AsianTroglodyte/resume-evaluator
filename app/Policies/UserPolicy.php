<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isGlobalAdmin();;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $userToLookAt): bool
    {

        if($user->id === $userToLookAt->id) {
            return true;
        }

        return $this->viewAny($user)
            || $userToLookAt->modulesPartOf() // if user is an instructor in any 
            ->whereHas('instructors', fn ($query) => $query->whereKey($user->id))
            ->exists();
    }

    // return $jobListing->assignments()
    //      ->whereHas('assignees', fn ($query) => $query->whereKey($user->id))
    //      ->exists();

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
