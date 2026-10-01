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

        return redirect()
            ->route('dashboard.modules.assignments.show', [$module, $assignment])
            ->with('claimStatus', 'Job listing claimed.');
    }

    public function destroy(Request $request, Module $module, Assignment $assignment): RedirectResponse
    {
        $assignment->claimFor($request->user())->delete();

        return redirect()
            ->route('dashboard.modules.assignments.show', [$module, $assignment])
            ->with('claimStatus', 'Claim released.');
    }
}
