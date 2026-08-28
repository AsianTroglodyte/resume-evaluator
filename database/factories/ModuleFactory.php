<?php

namespace Database\Factories;

use App\Enums\ModuleMembershipStatus;
use App\Enums\RoleInModule;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    protected $model = Module::class;

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

    public function createdBy(User $user): static
    {
        return $this->state(fn () => ['created_by_user_id' => $user->id]);
    }

    public function withMembers(Collection|array|User $users): static
    {
        $users = Collection::wrap($users);

        return $this->afterCreating(function (Module $module) use ($users): void {
            $module->members()->attach($users->pluck('id'), [
                'role_in_module' => RoleInModule::Student,
                'status' => ModuleMembershipStatus::Active,
                'added_by_user_id' => $module->created_by_user_id,
            ]);
        });
    }

    public function withInstructor(Collection|array|User $users): static
    {
        $users = Collection::wrap($users);

        return $this->afterCreating(function (Module $module) use ($users): void {
            $module->members()->attach($users->pluck('id'), [
                'role_in_module' => RoleInModule::Instructor,
                'status' => ModuleMembershipStatus::Active,
                'added_by_user_id' => $module->created_by_user_id,
            ]);
        });
    }
}
