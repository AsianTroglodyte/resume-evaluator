<?php

namespace Database\Factories;

use App\Models\group_membership;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<group_membership>
 */
class GroupMembershipFactory extends Factory
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
            'group_id' => Group::factory(),
            'user_id' => User::factory(),
            'role_in_group' => 'user',
            'status' => 'idk',
            'added_by_user_id' => User::factory()->admin(),
            'removed_by_user_id' => null,
            'updated_at' => now(),
            'joined_at' => now(),
            'removed_at' => null,
        ];
    }

    public function group(Group $group): static 
    {
        return $this->state(fn () => ['group_id' => $group->id]);
    }

    public function user(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function addedBy(User $user): static
    {
        // Make sure to add checks if the user is an admin or
        // instructor of group
        // if ($user->global_role !== "admin") {
        // }
        return $this->state(fn () => ['added_by_user_id' => $user->id]);
    }

    public function removedByUser(User $user): static
    {
        // Make sure to add checks if the user is an admin or instructo
        // if ($user->global_role !== "admin") {
        // 
        // }
        return $this->state(fn () => ['removed_by_user_id' => $user->id]);
    }

    public function instructor(): static
    {
        return $this->state(fn () => ['role_in_group' => 'instructor']);
    }

}