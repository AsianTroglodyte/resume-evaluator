<?php

namespace App\Support;

use App\Models\JobListingClaim;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Releases claims on assignments the users are no longer given (group move, ungrouping,
 * removal, re-targeting). Claims backing a submission are kept; the submission is frozen.
 */
class ReleaseInaccessibleClaims
{
    /**
     * @param  list<int>  $userIds
     */
    public function __invoke(int $moduleId, array $userIds): void
    {
        User::query()->whereKey($userIds)->each(function (User $user) use ($moduleId): void {
            JobListingClaim::query()
                ->where('user_id', $user->id)
                ->whereHas('assignment', fn (Builder $assignment) => $assignment
                    ->where('module_id', $moduleId)
                    ->whereNot(fn (Builder $given) => $given->givenTo($user)))
                ->whereNotExists(fn ($submission) => $submission->selectRaw('1')
                    ->from('submissions')
                    ->whereColumn('submissions.assignment_id', 'job_listing_claims.assignment_id')
                    ->whereColumn('submissions.user_id', 'job_listing_claims.user_id'))
                ->delete();
        });
    }
}
