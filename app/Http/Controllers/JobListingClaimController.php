<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\JobListing;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobListingClaimController extends Controller
{
    /**
     * Claim a listing, or switch the student's existing claim to it (first come, first served).
     */
    public function update(Request $request, Module $module, Assignment $assignment): RedirectResponse
    {
        $validated = $request->validateWithBag('claim', [
            'job_listing_id' => ['required', 'integer'],
            'workspace_id' => ['nullable', 'integer'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($assignment, $user, $validated): void {
            // Locking the listing row serialises concurrent claims on it, so capacity can't be oversold.
            JobListing::query()->whereKey($validated['job_listing_id'])->lockForUpdate()->first();

            $jobListing = $assignment->claimableJobListings()
                ->where('job_listings.id', $validated['job_listing_id'])
                ->first();

            if ($jobListing === null) {
                throw ValidationException::withMessages([
                    'job_listing_id' => 'That job listing is not available for this assignment.',
                ])->errorBag('claim');
            }

            $currentClaim = $assignment->claimFor($user)->first();

            if ($currentClaim?->job_listing_id === $jobListing->id) {
                return;
            }

            if ($jobListing->isFull()) {
                throw ValidationException::withMessages([
                    'job_listing_id' => "{$jobListing->name} is full. Choose another listing.",
                ])->errorBag('claim');
            }

            $assignment->claims()->updateOrCreate(
                ['user_id' => $user->id],
                ['job_listing_id' => $jobListing->id],
            );
        });

        return $this->redirectAfterClaimChange($request, $module, $assignment)
            ->with('claimStatus', 'Job listing claimed.')
            ->with('job_listing_id', (int) $validated['job_listing_id']);
    }

    public function destroy(Request $request, Module $module, Assignment $assignment): RedirectResponse
    {
        $assignment->claimFor($request->user())->delete();

        return $this->redirectAfterClaimChange($request, $module, $assignment)
            ->with('claimStatus', 'Claim released.');
    }

    /**
     * Claims can be changed from the assignment page or the workspace's "browse all listings"
     * modal; return to whichever the student came from (only their own workspace).
     */
    private function redirectAfterClaimChange(Request $request, Module $module, Assignment $assignment): RedirectResponse
    {
        $workspace = $request->filled('workspace_id')
            ? $request->user()->workspaces()->find($request->integer('workspace_id'))
            : null;

        return $workspace
            ? redirect()->route('dashboard.workspaces.show', $workspace)
            : redirect()->route('dashboard.modules.assignments.show', [$module, $assignment]);
    }
}
