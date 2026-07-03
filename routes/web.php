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
use Illuminate\Support\Facades\Http;
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
            'scans' => [
                [
                    'id' => 103,
                    'status' => 'pending',
                    'job_description_label' => null,
                    'match_percent' => null,
                    'keyword_match' => null,
                    'quality_eval' => null,
                    'created_at' => 'Mar 23, 2026 · 9:14 AM',
                    'resume_text_preview' => null,
                    'job_description_preview' => null,
                ],
                [
                    'id' => 102,
                    'status' => 'completed',
                    'job_description_label' => 'Software Engineering Intern — RiverTech',
                    'match_percent' => 74,
                    'keyword_match' => 68,
                    'quality_eval' => 'Solid structure and relevant coursework. Add more quantified project outcomes and mirror the posting\'s language around REST APIs and Git workflows.',
                    'created_at' => 'Mar 22, 2026 · 4:30 PM',
                    'resume_text_preview' => "Alex Kim\nComputer Science, Junior\n\nExperience\n— Teaching Assistant, Data Structures...",
                    'job_description_preview' => 'We are looking for a Software Engineering Intern with experience in Python, REST APIs, and collaborative development using Git...',
                ],
                [
                    'id' => 101,
                    'status' => 'completed',
                    'job_description_label' => null,
                    'match_percent' => 82,
                    'keyword_match' => null,
                    'quality_eval' => 'Strong section headings and consistent formatting. Consider adding more quantified outcomes in experience bullets.',
                    'created_at' => 'Mar 20, 2026 · 11:02 AM',
                    'resume_text_preview' => "Alex Kim\nComputer Science, Junior\n\nExperience\n— Campus IT Support (earlier draft)...",
                    'job_description_preview' => null,
                ],
            ],
        ],
        [
            'id' => 2,
            'name' => 'Distributed Systems Roles',
            'updated_at' => 'Yesterday',
            'scans' => [
                [
                    'id' => 202,
                    'status' => 'completed',
                    'job_description_label' => 'Senior Backend Engineer (Distributed Systems)',
                    'match_percent' => 67,
                    'keyword_match' => 69,
                    'quality_eval' => 'Missing explicit mentions of Kafka and consensus protocols despite relevant project work. Experience bullets are strong but could better highlight scale and reliability themes from the posting.',
                    'created_at' => 'Mar 19, 2026 · 2:15 PM',
                    'resume_text_preview' => "Jordan Lee\nBackend Engineer\n\nBuilt microservices handling 10k req/s at...",
                    'job_description_preview' => 'Senior Backend Engineer to design distributed systems using Kafka, gRPC, and consensus protocols...',
                ],
                [
                    'id' => 201,
                    'status' => 'completed',
                    'job_description_label' => null,
                    'match_percent' => 71,
                    'keyword_match' => null,
                    'quality_eval' => 'Readable layout, but some bullets are long single-line paragraphs. Break complex achievements into shorter, scannable lines.',
                    'created_at' => 'Mar 10, 2026 · 10:48 AM',
                    'resume_text_preview' => "Jordan Lee\nBackend Engineer\n\nExperience\n— Platform team, API development...",
                    'job_description_preview' => null,
                ],
            ],
        ],
        [
            'id' => 3,
            'name' => 'Frontend Portfolio Refresh',
            'updated_at' => 'Mar 5, 2026',
            'scans' => [
                [
                    'id' => 301,
                    'status' => 'failed',
                    'job_description_label' => 'Frontend Developer — BlueWave Analytics',
                    'match_percent' => null,
                    'keyword_match' => null,
                    'quality_eval' => null,
                    'error_message' => 'Evaluation timed out. Try again with a shorter resume or job description.',
                    'created_at' => 'Mar 5, 2026 · 3:22 PM',
                    'resume_text_preview' => "Sam Rivera\nFrontend Developer...",
                    'job_description_preview' => 'Frontend Developer with React, TypeScript, and data visualization experience...',
                ],
            ],
        ],
    ];
}

/**
 * Sample evaluation payload for local UI development (replaced by session flash after a real run).
 *
 * @return array<string, mixed>
 */
function mockEvaluation(): array
{
    return [
        'quality_eval' => "Solid structure and relevant coursework. Add more quantified project outcomes and mirror the posting's language around REST APIs and Git workflows.\n\n• Lead with impact in your top experience bullet.\n• Your projects section could name specific frameworks from the job description.",
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
            ->map(function (array $workspace): array {
                $latestScan = $workspace['scans'][0] ?? null;

                return [
                    'id' => $workspace['id'],
                    'name' => $workspace['name'],
                    'scan_count' => count($workspace['scans']),
                    'latest_scan' => $latestScan,
                    'updated_at' => $workspace['updated_at'],
                ];
            })
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
            'previewEvaluation' => mockEvaluation(),
        ]);
    })->whereNumber('id')
        ->name('dashboard.workspaces.show');

    Route::post('/dashboard/workspaces/{id}', function (int $id) {
        $workspace = collect(mockWorkspaces())->firstWhere('id', $id);

        if ($workspace === null) {
            abort(404);
        }

        $response = Http::baseUrl(config('services.eval.url'))
            ->timeout(config('services.eval.timeout'))
            ->acceptJson()
            ->post('/evaluate', [
                'resume_text' => request()->resume_text,
                'job_description' => request()->job_description,
            ]);

        if ($response->failed()) {
            return redirect()
                ->route('dashboard.workspaces.show', $id)
                ->with('evaluation_error', 'Evaluation service could not complete the request.');
        }

        return redirect()
            ->route('dashboard.workspaces.show', $id)
            ->with('evaluation', $response->json());
    })->name('dashboard.workspaces.scans.store');

    Route::redirect('/dashboard/admin', '/dashboard/admin/users');

    Route::get('/dashboard/admin/users', function () {
        return view('dashboard.admin.users.index', [
            'users' => User::query()->orderBy('last_name', 'asc')->orderBy('first_name', 'asc')->get(),
        ]);
    })->name('dashboard.admin.users.index')
        ->can('viewAny', User::class);
});
