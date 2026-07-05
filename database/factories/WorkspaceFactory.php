<?php

namespace Database\Factories;

use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Workspace>
 */
class WorkspaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->jobTitle(),
            'user_id' => UserFactory::create()
            //
        ];
    }

    public function name(string $workspace_name): static
    {
        return $this->state( fn (array $attributes) => [
            'name' => $workspace_name
        ]);
    }

    public function user(): static 
    {
        return $this->state(fn (array $attributes) => [

        ]);
    }
}
