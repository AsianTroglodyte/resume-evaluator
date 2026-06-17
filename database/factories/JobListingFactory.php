<?php

namespace Database\Factories;

use App\Models\JobListing;
use App\Models\Module;
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
            'name' => fake()->jobTitle(),
            'description' => $this->faker->paragraph(),
            'module_id' => Module::factory(),
        ];
    }

    public function forModule(Module $module): static
    {
        return $this->state(fn () => ['module_id' => $module->id]);
    }
}
