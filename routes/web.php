<?php

use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\ModuleAssignmentsController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ModuleJobListingController;
use App\Http\Controllers\ModuleMembersController;
use App\Http\Controllers\ModuleSettingsController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkspaceController;
use App\Models\Assignment;
use App\Models\JobListing;
use App\Models\Module;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Sample evaluation payload for local UI development (replaced by session flash after a real run).
 *
 * @return array<string, mixed>
 */
if (! function_exists('mockEvaluation')) {
    function mockEvaluation(): array
    {
        return [
            'keyword_match' => 62.5,
            'matched_keywords' => [
                'Python',
                'FastAPI',
                'PostgreSQL',
                'Git',
                'REST APIs',
                'Linux',
            ],
            'missing_keywords' => [
                'Docker',
                'Kubernetes',
                'AWS',
                'CI/CD',
                'microservices',
                'agile',
                'Redis',
                'unit testing',
                'code review',
                'pair programming',
                'Terraform',
            ],
            'jd_keywords' => [
                'role' => 'Software Engineering Intern',
                'company' => 'RiverTech',
                'required_skills' => ['Python', 'FastAPI', 'Git', 'REST APIs'],
                'preferred_skills' => ['Docker', 'PostgreSQL', 'Linux'],
                'keywords' => ['Kubernetes', 'AWS', 'CI/CD', 'microservices', 'agile', 'Redis', 'unit testing', 'code review', 'pair programming', 'Terraform'],
            ],
            'ai_phrases' => [
                ['phrase' => 'leveraged', 'suggestion' => 'used'],
                ['phrase' => 'spearheaded', 'suggestion' => 'led'],
                ['phrase' => 'synergy', 'suggestion' => 'collaboration'],
                ['phrase' => 'in order to', 'suggestion' => 'to'],
            ],
            'enrichment' => [
                'analysis_summary' => 'Projects are described clearly, but internship experience bullets are vague and lack metrics or specific technologies.',
                'items_to_enrich' => [
                    [
                        'item_id' => 'exp_0',
                        'item_type' => 'experience',
                        'title' => 'Software Engineering Intern',
                        'subtitle' => 'RiverTech',
                        'current_description' => [
                            'Worked on backend features for the customer portal',
                            'Helped with API development and bug fixes',
                        ],
                        'weakness_reason' => 'Generic phrasing with no measurable impact or tech stack named.',
                    ],
                ],
                'questions' => [
                    [
                        'question_id' => 'q_0',
                        'item_id' => 'exp_0',
                        'question' => 'What specific metrics or outcomes improved from your work at RiverTech?',
                        'placeholder' => 'e.g., Reduced API response time by 35%, fixed 12 production bugs',
                    ],
                    [
                        'question_id' => 'q_1',
                        'item_id' => 'exp_0',
                        'question' => 'Which languages, frameworks, and tools did you use in this internship?',
                        'placeholder' => 'e.g., Python, FastAPI, PostgreSQL, Git, Docker',
                    ],
                ],
            ],
            'warnings' => [
                'Education is empty — skip only if that\'s intentional.',
            ],
        ];
    }
}

Route::get('/dashboard/workspaces/{workspace}/test', function (Workspace $workspace) {
    return response()->json([
        'hasPendingEvaluation' => $workspace->hasPendingEvaluation(),
    ]);
})->name('test');

Route::get('/', function () {
    return view('home');
})->name('home');

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

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('home')->with('message', 'Verification link sent!');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    Route::post('/logout', [SessionController::class, 'destroy'])->name('logout.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
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
            ->name('dashboard.modules.assignments.destroy')
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
            ->name('dashboard.modules.job-listings.destroy')
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
        Route::patch('/user/profile', 'updatePassword')
            ->name('user.password.update');
    });

    Route::controller(WorkspaceController::class)->group(function () {
        Route::get('/dashboard/workspaces', 'index')
            ->name('dashboard.workspaces.index');
        Route::post('/dashboard/workspaces', 'store')
            ->name('dashboard.workspaces.store');
        Route::get('/dashboard/workspaces/{workspace}', 'show')
            ->name('dashboard.workspaces.show')
            ->can('view', 'workspace');
        Route::delete('/dashboard/workspaces/{workspace}', 'destroy')
            ->name('dashboard.workspaces.destroy')
            ->can('delete', 'workspace');
        Route::patch('/dashboard/workspaces/{workspace}', 'update')
            ->name('dashboard.workspaces.update')
            ->can('update', 'workspace');
    });

    Route::controller(EvaluationController::class)->group(function () {
        Route::post('/workspaces/{workspace}/evaluation', 'storeForWorkspace')
            ->name('workspaces.evaluations.store');
        Route::post('/submissions/{assignment}/evaluation', 'storeForSubmission')
            ->name('submissions.evaluations.store');
        Route::delete('/submissions/{assignment}/evaluation', 'destroyForSubmission')
            ->name('submissions.evaluations.destroy');
    });

    Route::redirect('/dashboard/admin', '/dashboard/admin/users');

    Route::get('/dashboard/admin/users', function () {
        return view('dashboard.admin.users.index', [
            'users' => User::query()->orderBy('last_name', 'asc')->orderBy('first_name', 'asc')->get(),
        ]);
    })->name('dashboard.admin.users.index')
        ->can('viewAny', User::class);
});
