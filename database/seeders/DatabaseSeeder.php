<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\JobListing;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        $admin = User::factory()->admin()->password('admin')->create([
            'first_name' => 'Karan',
            'last_name' => 'Swansi',
            'email' => 'kswansi@southnern.edu',
        ]);

        // Basic users
        User::factory(10)->create([]);

        $groups = Group::factory(2)
            ->createdBy($admin)
            ->sequence(
                ['name' => 'Resume Workshop 2025', 'status' => 'Archived'],
                ['name' => 'Resume Workshop 2026'],
            )
            ->create();

        JobListing::factory(3)
            ->forGroup($groups[0])
            ->create();

        JobListing::factory(3)
            ->forGroup($groups[1])
            ->create();
    }
}
