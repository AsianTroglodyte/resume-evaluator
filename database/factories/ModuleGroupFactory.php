<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\ModuleGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModuleGroup>
 */
class ModuleGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'name' => fake()->unique()->words(2, true),
        ];
    }

    public function forModule(Module $module): static
    {
        return $this->state(fn (array $attributes) => [
            'module_id' => $module->id,
        ]);
    }
}
