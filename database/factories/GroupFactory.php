<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\GroupMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'name',
            'status' => 'active',
            'created_by_user_id' => User::factory()->admin(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Group $group): void {
            GroupMembership::firstOrCreate(
                ['group_id' => $group->id, 'user_id' => $group->created_by_user_id],
                [
                    'role_in_group' => 'instructor',
                    'status' => 'active',
                    'added_by_user_id' => $group->created_by_user_id,
                    'removed_by_user_id' => null,
                    'joined_at' => now(),
                    'removed_at' => null,
                ]
            );
        });
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn () => ['created_by_user_id' => $user->id]);
    }
}
