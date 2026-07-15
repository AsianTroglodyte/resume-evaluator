<?php

namespace Database\Factories;

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Models\Assignment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'module_id' => Module::factory(),
            'created_by_user_id' => User::factory()->admin(),
            'title' => 'Assignment '.$this->faker->unique()->numberBetween(1, 10_000),
            'description' => $this->faker->paragraph(),
            'due_date' => now()->addWeek(),
            'assignee_scope' => AssigneeScope::Everyone,
            'job_listing_source' => JobListingSource::External,
            'module_job_listing_scope' => ModuleJobListingScope::All,
            'allow_resubmission' => true,
        ];
    }

    public function forModule(Module $module): static
    {
        return $this->state(fn () => ['module_id' => $module->id]);
    }

    public function createdBy(User $user): static
    {
        return $this->state(fn () => ['created_by_user_id' => $user->id]);
    }
}
