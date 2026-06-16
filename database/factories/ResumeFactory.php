<?php

namespace Database\Factories;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Resume>
 */
class ResumeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'storage_path' => 'resumes/'.$this->faker->uuid().'.pdf',
            'original_name' => $this->faker->words(asText: true).'.pdf',
            'uploaded_at' => now(),
        ];
    }

    public function forUser(User $user)
    {
        return ['user_id' => $user->id];
    }
}
