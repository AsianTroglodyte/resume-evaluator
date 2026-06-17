<?php

namespace Database\Factories;

use App\Models\AssignmentAssignees;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentAssignees>
 */
class AssignmentAssigneesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
            'assignment_id' => Assignment::factory(),
            'user_id' => User::factory()
        ];
    }

    public function hasAssignment(Assignment $assignment): static
    {
        return $this->state( fn () => [
            'assignment_id' => $assignment->id
        ]);
    }

    public function hasUser(User $user): static
    {
        return $this->state( fn () => [
            'user_id' => $user->id
        ]);
    }

    
}
