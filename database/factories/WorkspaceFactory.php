<?php

namespace Database\Factories;

use App\Models\User;
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
            'user_id' => User::factory()->create([])
        ];
    }

    public function name(string $workspaceName): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $workspaceName
        ]);
    }

    public function user(int $userId): static 
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId
        ]);
    }
}
