<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

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

    public function configure(): static
    {
        return $this->afterCreating(function (Module $module): void {
            ModuleMembership::firstOrCreate(
                ['module_id' => $module->id, 'user_id' => $module->created_by_user_id],
                [
                    'role_in_module' => 'instructor',
                    'status' => 'active',
                    'added_by_user_id' => $module->created_by_user_id,
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
