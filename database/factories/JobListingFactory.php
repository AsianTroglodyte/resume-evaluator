<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\JobListing;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobListing>
 */
class JobListingFactory extends Factory
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
            // "group_id" => constrained('groups'),
            'name' => fake()->jobTitle(),
            'description' => $this->faker->paragraph(),
            'group_id' => Group::factory(),
        ];
    }

    public function forGroup(Group $group): static
    {
        return $this->state(fn () => ['group_id' => $group->id]);
    }
}
