<?php

namespace App\Support;

use App\Enums\JobListingSource;
use App\Models\Assignment;
use App\Models\JobListing;
use App\Models\JobListingClaim;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Listings a user may practise against in a workspace, grouped by assignment: every listing
 * allowed on an assignment they are given. Their claimed listing is flagged and sorted first,
 * and assignments with a claim come first. Read-only: practice never touches claims (ADR 0007).
 */
class PracticeJobContexts
{
    /**
     * @return Collection<int, array{assignment: Assignment, listings: Collection<int, JobListing>, claimedJobListingId: ?int, hasSubmitted: bool}>
     */
    public function __invoke(User $user): Collection
    {
        $claimedJobListingIds = JobListingClaim::query()
            ->where('user_id', $user->id)
            ->pluck('job_listing_id', 'assignment_id');

        return Assignment::query()
            ->givenTo($user)
            ->where('job_listing_source', '!=', JobListingSource::External->value)
            ->with('module')
            ->withExists(['submissions as submitted_by_user' => fn ($submissions) => $submissions->where('user_id', $user->id)])
            ->get()
            ->map(function (Assignment $assignment) use ($claimedJobListingIds): array {
                $claimedJobListingId = $claimedJobListingIds->get($assignment->id);

                return [
                    'assignment' => $assignment,
                    'listings' => $assignment->claimableJobListings()->get()
                        ->sortBy(fn (JobListing $listing) => $listing->id === $claimedJobListingId ? 0 : 1)
                        ->values(),
                    'claimedJobListingId' => $claimedJobListingId,
                    'hasSubmitted' => (bool) $assignment->submitted_by_user,
                ];
            })
            ->filter(fn (array $context) => $context['listings']->isNotEmpty())
            ->sortBy([
                fn (array $a, array $b) => ($a['claimedJobListingId'] === null) <=> ($b['claimedJobListingId'] === null),
                fn (array $a, array $b) => [$a['assignment']->module->name, $a['assignment']->title]
                    <=> [$b['assignment']->module->name, $b['assignment']->title],
            ])
            ->values();
    }

    public function allows(User $user, int $jobListingId): ?JobListing
    {
        return ($this)($user)
            ->flatMap(fn (array $context) => $context['listings'])
            ->firstWhere('id', $jobListingId);
    }
}
