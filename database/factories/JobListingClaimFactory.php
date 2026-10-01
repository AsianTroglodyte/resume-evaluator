<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\JobListing;
use App\Models\JobListingClaim;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobListingClaim>
 */
class JobListingClaimFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'job_listing_id' => JobListing::factory(),
            'user_id' => User::factory(),
        ];
    }

    public function on(Assignment $assignment, JobListing $jobListing): static
    {
        return $this->state(fn () => [
            'assignment_id' => $assignment->id,
            'job_listing_id' => $jobListing->id,
        ]);
    }

    public function by(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }
}
