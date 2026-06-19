<?php

use App\Enums\AssigneeScope;
use App\Enums\JobListingSource;
use App\Enums\ModuleJobListingScope;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Arr as SupportArr;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/dashboard/modules', function () {
    $modules = Module::all();

    return view('dashboard.modules.index', [
        'modules' => $modules,
    ]);
})->name('dashboard.modules.index');

Route::get('/dashboard/modules/create', function () {
    return view('dashboard.modules.create', []);
})->name('dashboard.modules.create');

Route::post('/dashboard/modules', function () {
    request()->validate([
        'name' => ['required', 'min:3'],
    ]);

    Module::create([
        'name' => request('name'),
        'created_by_user_id' => 1,
    ]);

    return redirect()->route('dashboard.modules.index');
})->name('dashboard.modules.store');

Route::get('/dashboard/modules/{id}', function ($id) {
    $module = Module::findOrFail($id);

    $job_listings = $module->jobListings;

    $assignments = $module
        ->assignments()
        ->with('assignees', 'jobListings')
        ->get();

    return view('dashboard.modules.show', [
        'job_listings' => $job_listings,
        'module' => $module,
        'assignments' => $assignments,
    ]);
})->name('dashboard.modules.show');

Route::get('/dashboard/modules/{id}/participants', function ($id) {
    $module = Module::findOrFail($id);

    $participants = $module
        ->users()
        ->orderBy('last_name')
        ->orderBy('first_name')
        ->get();

    return view('dashboard.modules.participants', [
        'module' => $module,
        'participants' => $participants,
    ]);
})->name('dashboard.modules.participants');

Route::get('/dashboard/modules/{id}/assignment/create', function ($id) {
    $module = Module::findOrFail($id);

    $job_listings = $module->jobListings;
    $users = $module->users;

    return view('dashboard.modules.assignment-create', [
        'module' => $module,
        'job_listings' => $job_listings,
        'users' => $users,

    ]);
})->name('dashboard.modules.assignments.create');

Route::post('/dashboard/modules/{id}/assignment/create', function ($id) {
    $module = Module::findOrFail($id);

    // [] ??;


    $validated = request()->validate([
        'title' => ['required', 'string', 'min:3', 'max:255'],
        // made by an actual instructor/admin
        'due_at' => ['nullable', 'date', 'after:now'],
        'description' => ['nullable', 'string', 'max:500'],
        'job_listing_source' => ['required', Rule::enum(JobListingSource::class)],
        'module_job_listing_scope' => ['required', Rule::enum(ModuleJobListingScope::class)],
        'assignee_scope' => ['required', Rule::enum(AssigneeScope::class)],
        'allow_resubmission' => ['required', 'boolean'],
        'job_listing_ids' => ['array'],
        'job_listing_ids.*' => [
            'required',
            'integer',
            Rule::exists('job_listings', 'id')->where('module_id', $module->id)],
        'assignee_ids' => ['array'],
        'assignee_ids.*' => [
            'required',
            'integer',
            Rule::exists('module_memberships', 'user_id')->where('module_id', $module->id)],
    ]);

    $validated['due_at'] = $validated['due_at'] ?? null;

    $assignmentInfo = SupportArr::only($validated, [
        'title',
        'due_at',
        'description',
        'job_listing_source',
        'module_job_listing_scope',
        'assignee_scope',
        'allow_resubmission',
    ]);

    $jobListingIds = $validated['job_listing_ids'] ?? [];
    $assigneeIds = $validated['assignee_ids'] ?? [];

    // dd([$assignmentInfo, $jobListingIds, $assigneeIds, request()->all()]);

    // dd(request()->all());
    $assignment = $module->assignments()->create([
        // ...$validated,
        'created_by_user_id' => 1,
        'module_id' => $id,
        'title' => $assignmentInfo['title'],
        'description' => $assignmentInfo['description'],
        'due_at' => $assignmentInfo['due_at'],
        'assignee_scope' => AssigneeScope::from($assignmentInfo['assignee_scope']),
        'job_listing_source' => JobListingSource::from($assignmentInfo['job_listing_source']),
        'module_job_listing_scope' => ModuleJobListingScope::from($assignmentInfo['module_job_listing_scope']),
        'allow_resubmission' => $assignmentInfo['allow_resubmission'],
    ]);

    foreach ($jobListingIds as $jobListingId) {
        $assignment->assignmentAllowedJobListings()->create([
            'job_listing_id' => $jobListingId,
            'assignment_id' => $assignment["id"]
        ]);
    };

    foreach ($assigneeIds as $assigneeId) {
        $assignment->assignmentAssignees()->create([
            'user_id' => $assigneeId,
            'assignment_id' => $assignment["id"]
        ]);
    };

    return redirect()->route('dashboard.modules.assignments.create', $id);
    // dashboard.modules.show
})->name('dashboard.modules.assignments.store');
//

Route::post('/dashboard/modules/{id}/job-listings', function ($id) {
    $module = Module::findOrFail($id);

    $validated = request()->validate([
        'name' => ['required', 'string', 'min:3'],
        'description' => ['required', 'string'],
    ]);

    $module->jobListings()->create($validated);

    return redirect()->route('dashboard.modules.show', $id);
})->name('dashboard.modules.job-listings.store');

Route::get('/dashboard/resumes', function () {
    $evaluations = [
        [
            'id' => 101,
            'name' => 'Resume 1',
            'ats_friendliness' => 90,
            'keyword_match' => null,
            'modules' => [
                ['id' => 1, 'name' => 'Module 1'],
            ],
        ],
        [
            'id' => 102,
            'name' => 'Senior Backend Engineer (Distributed Systems)',
            'ats_friendliness' => 67,
            'keyword_match' => 69,
            'modules' => [
                ['id' => 2, 'name' => 'Senior Seminar W25'],
                ['id' => 4, 'name' => 'Distributed Systems Cohort'],
            ],
        ],
        [
            'id' => 103,
            'name' => 'Frontend Developer - BlueWave Analytics',
            'ats_friendliness' => 50,
            'keyword_match' => 50,
            'modules' => [
                ['id' => 3, 'name' => 'Senior Seminar W24'],
            ],
        ],
    ];

    return view('dashboard.resumes.index', [
        'evaluations' => $evaluations,
    ]);
})->name('dashboard.resumes.index');

Route::get('/dashboard/resumes/{id}', function ($id) {
    $evaluations = [
        [
            'id' => 101,
            'name' => 'Resume 1',
            'ats_friendliness' => 90,
            'keyword_match' => null,
            'modules' => [
                ['id' => 1, 'name' => 'Module 1'],
            ],
        ],
        [
            'id' => 102,
            'name' => 'Senior Backend Engineer (Distributed Systems)',
            'ats_friendliness' => 67,
            'keyword_match' => 69,
            'modules' => [
                ['id' => 2, 'name' => 'Senior Seminar W25'],
                ['id' => 4, 'name' => 'Distributed Systems Cohort'],
            ],
        ],
        [
            'id' => 103,
            'name' => 'Frontend Developer - BlueWave Analytics',
            'ats_friendliness' => 50,
            'keyword_match' => 50,
            'modules' => [
                ['id' => 3, 'name' => 'Senior Seminar W24'],
            ],
        ],
    ];
    $evaluation = collect($evaluations)->firstWhere('id', $id);

    return view('dashboard.resumes.show', [
        'title' => $evaluation['name'],
        'evaluation' => $evaluation['ats_friendliness'],
        'keyword_match' => $evaluation['keyword_match'],
        'modules' => $evaluation['modules'],
    ]);
})->name('dashboard.resumes.show');

Route::get('/testdb', function () {
    $modules = Module::all();
    $users = User::all();
    dd($modules, $users);
});

Route::redirect('/dashboard/admin', '/dashboard/admin/users');

Route::get('/dashboard/admin/users', function () {
    return view('dashboard.admin.users.index', [
        'users' => User::query()->orderBy('last_name')->orderBy('first_name')->get(),
    ]);
})->name('dashboard.admin.users.index');
