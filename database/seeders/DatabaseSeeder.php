<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\JobListing;
use App\Models\User;
use App\Models\Assignment;
use App\Models\AssignmentAssignees;
use App\Models\GroupMembership;
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
        $basic_users = User::factory(10)->create([]);

        // Creating Groups
        $groups = Group::factory(2)
            ->createdBy($admin)
            ->sequence(
                ['name' => 'Resume Workshop 2025', 'status' => 'Archived'],
                ['name' => 'Resume Workshop 2026'],
            )
            ->create();


        // Creating Job listing for each group
        // echo $groups;

        // Creating Assignments for each group
    
        // Adding users to Groups and assigning assignments to them
        // group 0 
        // for ()
        for ($userId = 0; $userId < 3; $userId ++) {
            GroupMembership::factory()
                ->group($groups[0])
                ->user($basic_users[$userId])
                ->addedBy($admin)
                ->create();

            $job_listing = JobListing::factory()
                ->forGroup($groups[0])
                ->create();                

            $assignment = Assignment::factory()
                ->forGroup($groups[0])
                ->createdBy($admin)
                ->create();

            AssignmentAssignees::factory()
                ->hasAssignment($assignment)
                ->hasUser($basic_users[$userId])
                ->create();
        }

        // group 1
        for ($userId = 3; $userId < 6; $userId ++) {
            GroupMembership::factory()
                ->group($groups[1])
                ->user($basic_users[$userId])
                ->addedBy($admin)
                ->create();
            
            $job_listing = JobListing::factory()
                ->forGroup($groups[1])
                ->create();

            $assignment = Assignment::factory()
                ->forGroup($groups[1])
                ->createdBy($admin)
                ->create();
            
            AssignmentAssignees::factory()
                ->hasAssignment($assignment)
                ->hasUser($basic_users[$userId])
                ->create();
        }
    }
}
