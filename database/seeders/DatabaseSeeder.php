<?php

namespace Database\Seeders;

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Enums\ModuleStatus;
use App\Models\Assignment;
use App\Models\AssignmentAllowedJobListings;
use App\Models\AssignmentAssignees;
use App\Models\JobListing;
use App\Models\Module;
use App\Models\ModuleMembership;
use App\Models\User;
use App\Models\Workspace;
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
            'status' => 'archived',
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
        ],
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->password('password')->create([
            'first_name' => 'Karan',
            'last_name' => 'Swansi',
            'email' => 'kswansi@southern.edu',
        ]);

        Workspace::factory()->user($admin->id)->create([]);
        User::factory(5)->create([]);

        $testUser1 = User::factory()->admin()->password('password')->create([
            'first_name' => 'Robert',
            'last_name' => 'Ordonez',
            'email' => 'rordonez@southern.edu',
        ]);

        $this->regularUserTester(
            'Karan',
            'Swansi',
            'password',
            'kswansi2002@gmail.com',
            $admin,
        );

        Workspace::factory()->name('test workspace')->user($testUser1->id)->create();

        $this->seedModulesFromConfig($admin);
    }

    /**
     * @param  array{
     *     name: string,
     *     status: string,
     *     lone_members: int,
     *     lone_job_listings: int,
     *     assignments: list<array{
     *         assignees: int,
     *         job_listings: int,
     *     }>,
     * }  $config
     */
    private function seedModuleFromConfig(User $admin, array $config): Module
    {
        $module = Module::factory()
            ->createdBy($admin)
            ->create([
                'name' => $config['name'],
                'status' => $config['status'],
            ]);

        $loneMembers = User::factory($config['lone_members'])->password('password')->create();

        foreach ($loneMembers as $loneMember) {
            Workspace::factory()->user($loneMember->id)->create();
        }

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
                $assignmentAssignees = User::factory($assignmentSpec['assignees'])
                    ->password('password')
                    ->create();

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

        return $module;
    }

    private function seedModulesFromConfig(User $admin): void
    {
        foreach ($this->modulesConfig as $config) {
            $this->seedModuleFromConfig($admin, $config);
        }
    }

    private function regularUserTester(
        string $firstName,
        string $lastName,
        string $password,
        string $email,
        User $admin,
    ): User {
        $testUser = User::factory()->password($password)->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ]);

        Workspace::factory()->name('test workspace')->user($testUser->id)->create();

        $module = Module::factory()
            ->createdBy($admin)
            ->create([
                'name' => 'Regular User Test Module',
                'status' => ModuleStatus::Active,
            ]);

        $classmates = User::factory(4)->password('password')->create();

        foreach ([$testUser, ...$classmates] as $member) {
            ModuleMembership::factory()
                ->module($module)
                ->user($member)
                ->addedBy($admin)
                ->state([
                    'role_in_module' => 'student',
                    'status' => 'active',
                ])
                ->create();
        }

        $jobListings = JobListing::factory(3)
            ->forModule($module)
            ->create();

        $assignment = Assignment::factory()
            ->forModule($module)
            ->createdBy($admin)
            ->create([
                'title' => 'Practice Resume Submission',
                'assignee_scope' => AssigneeScope::Selected,
                'job_listing_source' => JobListingSource::Module,
                'module_job_listing_scope' => ModuleJobListingScope::Selected,
            ]);

        foreach ($jobListings as $jobListing) {
            AssignmentAllowedJobListings::factory()
                ->withAssignment($assignment)
                ->withJobListing($jobListing)
                ->create();
        }

        AssignmentAssignees::factory()
            ->hasAssignment($assignment)
            ->hasUser($testUser)
            ->create();

        foreach ($classmates->take(2) as $classmate) {
            AssignmentAssignees::factory()
                ->hasAssignment($assignment)
                ->hasUser($classmate)
                ->create();
        }

        return $testUser;
    }
}
