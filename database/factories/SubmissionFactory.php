<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "user_id" => User::factory(),
            "assignment_id" => Assignment::factory(),
            "assignment_version" => 1,
            "due_date_snapshot" => null,
        ];
    }

    public function withUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            "user_id" => $user->id
        ]);
    }

    public function forAssignment(Assignment $assignment): static
    {
        return $this->state(fn (array $attributes) => [
            "assignment_id" => $assignment->id
        ]);
    }
}
