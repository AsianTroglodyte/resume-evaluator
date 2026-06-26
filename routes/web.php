<?php

use App\Http\Controllers\ModuleAssignmentsController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleJobListingController;
use App\Http\Controllers\ModuleMembersController;
use App\Http\Controllers\ModuleSettingsController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Models\Assignment;
use App\Models\JobListing;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * @return list<array<string, mixed>>
 */
function mockWorkspaces(): array
{
    return [
        [
            'id' => 1,
            'name' => 'Summer Internship Prep',
            'updated_at' => '2 hours ago',
            'versions' => [
                [
                    'id' => 11,
                    'original_name' => 'resume_v1.pdf',
                    'uploaded_at' => 'Mar 12, 2026',
                    'is_latest' => false,
                ],
                [
                    'id' => 12,
                    'original_name' => 'resume_v2.pdf',
                    'uploaded_at' => 'Mar 18, 2026',
                    'is_latest' => false,
                ],
                [
                    'id' => 13,
                    'original_name' => 'resume_v3.pdf',
                    'uploaded_at' => 'Mar 22, 2026',
                    'is_latest' => true,
                ],
            ],
            'scans' => [
                [
                    'id' => 101,
                    'version_name' => 'resume_v3.pdf',
                    'job_context_label' => 'General scan (no job description)',
                    'label' => 'General scan',
                    'ats_score' => 82,
                    'keyword_match' => null,
                    'created_at' => 'Mar 22, 2026',
                    'feedback_preview' => 'Strong section headings and consistent formatting. Consider adding more quantified outcomes in experience bullets.',
                ],
            ],
        ],
        [
            'id' => 2,
            'name' => 'Distributed Systems Roles',
            'updated_at' => 'Yesterday',
            'versions' => [
                [
                    'id' => 21,
                    'original_name' => 'backend_resume.pdf',
                    'uploaded_at' => 'Mar 10, 2026',
                    'is_latest' => true,
                ],
            ],
            'scans' => [
                [
                    'id' => 201,
                    'version_name' => 'backend_resume.pdf',
                    'job_context_label' => 'General scan (no job description)',
                    'label' => 'General scan',
                    'ats_score' => 71,
                    'keyword_match' => null,
                    'created_at' => 'Mar 10, 2026',
                    'feedback_preview' => 'Readable layout, but some bullets are long single-line paragraphs.',
                ],
                [
                    'id' => 202,
                    'version_name' => 'backend_resume.pdf',
                    'job_context_label' => 'Senior Backend Engineer (Distributed Systems)',
                    'label' => 'Senior Backend Engineer',
                    'ats_score' => 67,
                    'keyword_match' => 69,
                    'created_at' => 'Mar 19, 2026',
                    'feedback_preview' => 'Missing explicit mentions of Kafka and consensus protocols despite relevant project work.',
                ],
            ],
        ],
        [
            'id' => 3,
            'name' => 'Frontend Portfolio Refresh',
            'updated_at' => 'Mar 5, 2026',
            'versions' => [
                [
                    'id' => 31,
                    'original_name' => 'frontend_dev_resume.docx',
                    'uploaded_at' => 'Mar 4, 2026',
                    'is_latest' => true,
                ],
            ],
            'scans' => [
                [
                    'id' => 301,
                    'version_name' => 'frontend_dev_resume.docx',
                    'job_context_label' => 'Frontend Developer — BlueWave Analytics',
                    'label' => 'BlueWave Analytics',
                    'ats_score' => 50,
                    'keyword_match' => 50,
                    'created_at' => 'Mar 5, 2026',
                    'feedback_preview' => 'Several required stack keywords are absent. Skills section could mirror the posting language more closely.',
                ],
            ],
        ],
    ];
}

Route::get('/', function () {
    return view('home');
});

Route::middleware('guest')->group(function () {
    Route::controller(RegisteredUserController::class)->group(function () {
        Route::get('/register', 'create')
            ->name('register');
        Route::post('/register', 'post')
            ->name('register.post');
    });

    Route::controller(SessionController::class)->group(function () {
        Route::get('/login', 'create')
            ->name('login');
        Route::post('/login', 'store')
            ->name('login.store');
    });
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout.destroy');

    Route::controller(ModuleController::class)->group(function () {
        Route::get('/dashboard/modules', 'index')
            ->name('dashboard.modules.index');
        Route::post('/dashboard/modules', 'store')
            ->name('dashboard.modules.store')
            ->can('create', Module::class);
        Route::get('/dashboard/modules/create', 'create')
            ->name('dashboard.modules.create')
            ->can('create', Module::class);
        Route::delete('/dashboard/modules/{module}', 'destroy')
            ->name('dashboard.modules.destroy')
            ->can('delete', Module::class);
        Route::get('/dashboard/modules/{module}', 'show')
            ->name('dashboard.modules.show')
            ->can('view', 'module');
    });

    Route::controller(ModuleMembersController::class)->group(function () {
        Route::get('/dashboard/modules/{module}/members/index', 'index')
            ->name('dashboard.modules.members.index')
            ->can('manageUsers', 'module');
        Route::post('/dashboard/modules/{module}/members/index', 'store')
            ->name('dashboard.modules.members.store')
            ->can('manageUsers', 'module');
        Route::delete('/dashboard/modules/{module}/members/index', 'destroy')
            ->name('dashboard.modules.members.destroy')
            ->can('manageUsers', 'module');
    });

    Route::controller(ModuleAssignmentsController::class)->group(function () {
        Route::get('/dashboard/modules/{module}/assignment/create', 'create')
            ->name('dashboard.modules.assignments.create')
            ->can('create', [Assignment::class, 'module']);
        Route::post('/dashboard/modules/{module}/assignment/create', 'store')
            ->name('dashboard.modules.assignments.store')
            ->can('create', [Assignment::class, 'module']);
        Route::get('/dashboard/modules/{module}/assignment/{assignment}', 'show')
            ->scopeBindings()
            ->name('dashboard.modules.assignments.show')
            ->can('view', 'assignment');
        Route::get('/dashboard/modules/{module}/assignment/{assignment}/edit', 'edit')
            ->scopeBindings()
            ->name('dashboard.modules.assignments.edit')
            ->can('update', 'assignment');
        Route::patch('/dashboard/modules/{module}/assignment/{assignment}', 'update')
            ->name('dashboard.modules.assignments.update')
            ->can('update', 'assignment');
        Route::delete('/dashboard/modules/{module}/assignment/{assignment}', 'destroy')
            ->scopeBindings()
            ->name('dashboard.modules.assignments.delete')
            ->can('delete', 'assignment');
    });

    Route::controller(ModuleJobListingController::class)->group(function () {
        Route::post('/dashboard/modules/{module}/job-listings', 'store')
            ->name('dashboard.modules.job-listings.store')
            ->can('create', [JobListing::class, 'module']);
        Route::patch('/dashboard/modules/{module}/job-listings/{jobListing}', 'update')
            ->scopeBindings()
            ->name('dashboard.modules.job-listings.update')
            ->can('update', 'jobListing');
        Route::delete('/dashboard/modules/{module}/job-listings/{jobListing}', 'destroy')
            ->scopeBindings()
            ->name('dashboard.modules.job-listings.delete')
            ->can('delete', 'jobListing');
    });

    Route::controller(ModuleSettingsController::class)->group(function () {
        Route::get('/dashboard/modules/{module}/settings/index', 'index')
            ->name('dashboard.modules.settings.index')
            ->can('update', 'module');
        Route::patch('/dashboard/modules/{module}/settings/index', 'update')
            ->name('dashboard.modules.settings.update')
            ->can('update', 'module');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('/user/profile', 'profile')
            ->name('user.profile');
        Route::get('/user/show/{user}', 'show')
            ->name('user.show')
            ->can('view', 'user');
    });

    Route::redirect('/dashboard/resumes', '/dashboard/workspaces');
    Route::redirect('/dashboard/resumes/{id}', '/dashboard/workspaces/{id}');

    Route::get('/dashboard/workspaces', function () {
        $workspaces = collect(mockWorkspaces())
            ->map(fn (array $workspace): array => [
                'id' => $workspace['id'],
                'name' => $workspace['name'],
                'version_count' => count($workspace['versions']),
                'scan_count' => count($workspace['scans']),
                'latest_version' => collect($workspace['versions'])->last(),
                'latest_scan' => collect($workspace['scans'])->last(),
                'updated_at' => $workspace['updated_at'],
            ])
            ->values()
            ->all();

        return view('dashboard.workspaces.index', [
            'workspaces' => $workspaces,
        ]);
    })->name('dashboard.workspaces.index');

    Route::get('/dashboard/workspaces/{id}', function (int $id) {
        $workspace = collect(mockWorkspaces())->firstWhere('id', $id);

        if ($workspace === null) {
            abort(404);
        }

        return view('dashboard.workspaces.show', [
            'workspace' => $workspace,
        ]);
    })
    ->whereNumber('id')
    ->name('dashboard.workspaces.show');

    // Route::get('/testdb', function () {
    //     $modules = Module::all();
    //     $users = User::all();
    //     dd($modules, $users);
    // });

    Route::redirect('/dashboard/admin', '/dashboard/admin/users');

    Route::get('/dashboard/admin/users', function () {
        return view('dashboard.admin.users.index', [
            'users' => User::query()->orderBy('last_name', 'asc')->orderBy('first_name', 'asc')->get(),
        ]);
    })->name('dashboard.admin.users.index')
        ->can('viewAny', User::class);
});
