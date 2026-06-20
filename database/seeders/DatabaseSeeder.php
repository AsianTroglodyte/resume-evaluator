<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentAllowedJobListings;
use App\Models\AssignmentAssignees;
use App\Models\JobListing;
use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * @var list<array{
     *     name: string,
     *     status: string,
     *     lone_members: int,
     *     lone_job_listings: int,
     *     assignments: list<array{
     *         assignees: int,
     *         job_listings: int,
     *     }>,
     * }>
     */
    private array $modulesConfig = [
        [
            'name' => 'Resume Workshop 2025',
            'status' => 'Archived',
            'lone_members' => 9,
            'lone_job_listings' => 3,
            'assignments' => [
                ['assignees' => 3, 'job_listings' => 3],
                ['assignees' => 3, 'job_listings' => 3],
                ['assignees' => 3, 'job_listings' => 3],
            ],
        ],
        [
            'name' => 'Resume Workshop 2026',
            'status' => 'active',
            'lone_members' => 3,
            'lone_job_listings' => 3,
            'assignments' => [
                ['assignees' => 1, 'job_listings' => 3],
                ['assignees' => 1, 'job_listings' => 3],
                ['assignees' => 0, 'job_listings' => 0],
            ],
        ],
        [
            'name' => 'empty-ish tester',
            'status' => 'active',
            'lone_members' => 10,
            'lone_job_listings' => 4,
            'assignments' => [],
        ]
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->password('admin')->create([
            'first_name' => 'Karan',
            'last_name' => 'Swansi',
            'email' => 'kswansin@southern.edu',
        ]);

        User::factory(5)->create([]);

        // specific stable user to reference when we need to reference a user
        User::factory(1)->create([
            'first_name' => 'Robert',
            'last_name' => 'Ordonez',
            'email' => 'rordonez@southern.edu',            
        ]);

        foreach ($this->modulesConfig as $config) {
            $module = Module::factory()
                ->createdBy($admin)
                ->create([
                    'name' => $config['name'],
                    'status' => $config['status'],
                ]);

            $loneMembers = User::factory($config['lone_members'])->create();

            foreach ($loneMembers as $loneMember) {
                ModuleMembership::factory()
                    ->module($module)
                    ->user($loneMember)
                    ->addedBy($admin)
                    ->state([
                        'role_in_module' => 'student',
                        'status' => 'active',
                    ])
                    ->create();
            }

            JobListing::factory($config['lone_job_listings'])
                ->forModule($module)
                ->create();

            foreach ($config['assignments'] as $assignmentSpec) {
                $assignment = Assignment::factory()
                    ->forModule($module)
                    ->createdBy($admin)
                    ->create();

                if ($assignmentSpec['job_listings'] > 0) {
                    $assignmentJobListings = JobListing::factory($assignmentSpec['job_listings'])
                        ->forModule($module)
                        ->create();

                    foreach ($assignmentJobListings as $assignmentJobListing) {
                        AssignmentAllowedJobListings::factory()
                            ->withAssignment($assignment)
                            ->withJobListing($assignmentJobListing)
                            ->create();
                    }
                }

                if ($assignmentSpec['assignees'] > 0) {
                    $assignmentAssignees = User::factory($assignmentSpec['assignees'])->create();

                    foreach ($assignmentAssignees as $assignee) {
                        ModuleMembership::factory()
                            ->module($module)
                            ->user($assignee)
                            ->addedBy($admin)
                            ->state([
                                'role_in_module' => 'student',
                                'status' => 'active',
                            ])
                            ->create();

                        AssignmentAssignees::factory()
                            ->hasAssignment($assignment)
                            ->hasUser($assignee)
                            ->create();
                    }
                }
            }
        }
    }
}
