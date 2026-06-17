<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModuleMembership>
 */
class ModuleMembershipFactory extends Factory
{
    protected $model = ModuleMembership::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'user_id' => User::factory(),
            'role_in_module' => 'student',
            'status' => 'active',
            'added_by_user_id' => User::factory()->admin(),
            'removed_by_user_id' => null,
            'updated_at' => now(),
            'joined_at' => now(),
            'removed_at' => null,
        ];
    }

    public function module(Module $module): static
    {
        return $this->state(fn () => ['module_id' => $module->id]);
    }

    public function user(User $user): static
    {
        return $this->state(fn () => ['user_id' => $user->id]);
    }

    public function addedBy(User $user): static
    {
        return $this->state(fn () => ['added_by_user_id' => $user->id]);
    }

    public function removedByUser(User $user): static
    {
        return $this->state(fn () => ['removed_by_user_id' => $user->id]);
    }

    public function instructor(): static
    {
        return $this->state(fn () => ['role_in_module' => 'instructor']);
    }
}
