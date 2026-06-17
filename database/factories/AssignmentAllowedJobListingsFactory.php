<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\AssignmentAllowedJobListings;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentAllowedJobListings>
 */
class AssignmentAllowedJobListingsFactory extends Factory
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
            //
        ];
    }

    public function withAssignment(Assignment $assignment): static
    {
        return $this->state(fn () => [
            'assignment_id' => $assignment->id,
        ]);
    }

    public function withJobListing(JobListing $job_listing): static
    {
        return $this->state(fn () => [
            'job_listing_id' => $job_listing->id,
        ]);
    }
}
