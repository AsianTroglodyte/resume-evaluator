<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group_id' => Group::factory(),
            'created_by_user_id' => User::factory()->admin(),
            'title' => 'Assignment '.$this->faker->unique()->numberBetween(1, 10_000),
            'description' => $this->faker->paragraph(),
            'status' => 'pending',
            'due_at' => now()->addWeek(),
            'assignment_scope' => 'everyone',
            'job_listing_rule' => 'any',
            'allow_resubmission' => true,
        ];
    }

    public function forGroup(Group $group): static
    {
        return $this->state(fn () => ['group_id' => $group->id]);
    }

    public function createdBy(User $user): static 
    {
        return $this->state(fn () => ['created_by_user_id' => $user->id]);
    }
}
