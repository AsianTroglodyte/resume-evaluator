<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/login', function () {
    return view('auth.login');
});

Route::get('/register', function () {
    return view('auth.register');
});

Route::get('/dashboard/groups', function () {
    $groups = Group::all();

    return view('dashboard.groups.index', [
        'groups' => $groups,
    ]);
})->name('dashboard.groups.index');

Route::get('/dashboard/groups/create', function () {
    return view('dashboard.groups.create', []);
})->name('dashboard.groups.create');

Route::post('/dashboard/groups', function () {
    //
    request()->validate([
        'name' => ['required', 'min:3'],
    ]);

    Group::create([
        'name' => request('name'),
        // 'status' => 'active',
        'created_by_user_id' => 1,
    ]);

    // dd(request()->all());
    return redirect()->route('dashboard.groups.index');
})->name('dashboard.groups.store');

Route::get('/dashboard/groups/{id}', function ($id) {
    $group = Group::findOrFail($id);
    if ($group === null) {
        dd($group);
    }

    $job_listings = $group->jobListings;
    $assignments = $group->assignments;

    return view('dashboard.groups.show', [
        'job_listings' => $job_listings,
        'group' => $group,
        'assignments' => $assignments,
    ]);
}
)->name('dashboard.groups.show');

Route::get('/dashboard/groups/{id}/assignments/create', function ($id) {
    $group = Group::findOrFail($id);

    $job_listings = $group->jobListings;

    return view('dashboard.groups.assignment-create', [
        'group' => $group,
        'job_listings' => $job_listings,
    ]);
})->name('dashboard.groups.assignments.create');

Route::post('/dashboard/groups/{id}/assignment/create', function ($id) {
    // dd(request()->all());

    $group = Group::findOrFail($id);

    $validated = request()->validate([
        'title' => ['required', 'string', 'min:3'],
        'description' => ['required', 'string'],
    ]);

    $group->assignments()->create([
        ...$validated,
        'created_by_user_id' => 1,
        'status' => 'pending',
        'assignment_scope' => 'everyone',
        'job_listing_rule' => 'any',
        'allow_resubmission' => true,
    ]);

    return redirect()->route('dashboard.groups.show', $id);
})->name('dashboard.groups.assignments.store');

Route::post('/dashboard/groups/{id}/job-listings', function ($id) {
    $group = Group::findOrFail($id);

    $validated = request()->validate([
        'name' => ['required', 'string', 'min:3'],
        'description' => ['required', 'string'],
    ]);

    $group->jobListings()->create($validated);

    return redirect()->route('dashboard.groups.show', $id);
})->name('dashboard.groups.job-listings.store');

Route::get('/dashboard/resumes', function () {
    $evaluations = [
        [
            'id' => 101,
            'name' => 'Resume 1',
            'ats_friendliness' => 90,
            'keyword_match' => null,
            'groups' => [
                ['id' => 1, 'name' => 'Group 1'],
            ],
        ],
        [
            'id' => 102,
            'name' => 'Senior Backend Engineer (Distributed Systems)',
            'ats_friendliness' => 67,
            'keyword_match' => 69,
            'groups' => [
                ['id' => 2, 'name' => 'Senior Seminar W25'],
                ['id' => 4, 'name' => 'Distributed Systems Cohort'],
            ],
        ],
        [
            'id' => 103,
            'name' => 'Frontend Developer - BlueWave Analytics',
            'ats_friendliness' => 50,
            'keyword_match' => 50,
            'groups' => [
                ['id' => 3, 'name' => 'Senior Seminar W24'],
            ],
        ],
    ];

    return view('dashboard.resumes.index', [
        'evaluations' => $evaluations,
    ]);
});

Route::get('/dashboard/resumes/{id}', function ($id) {
    $evaluations = [
        [
            'id' => 101,
            'name' => 'Resume 1',
            'ats_friendliness' => 90,
            'keyword_match' => null,
            'groups' => [
                ['id' => 1, 'name' => 'Group 1'],
            ],
        ],
        [
            'id' => 102,
            'name' => 'Senior Backend Engineer (Distributed Systems)',
            'ats_friendliness' => 67,
            'keyword_match' => 69,
            'groups' => [
                ['id' => 2, 'name' => 'Senior Seminar W25'],
                ['id' => 4, 'name' => 'Distributed Systems Cohort'],
            ],
        ],
        [
            'id' => 103,
            'name' => 'Frontend Developer - BlueWave Analytics',
            'ats_friendliness' => 50,
            'keyword_match' => 50,
            'groups' => [
                ['id' => 3, 'name' => 'Senior Seminar W24'],
            ],
        ],
    ];
    $evaluation = collect($evaluations)->firstWhere('id', $id);

    return view('dashboard.resumes.show', [
        'title' => $evaluation['name'],
        'evaluation' => $evaluation['ats_friendliness'],
        'keyword_match' => $evaluation['keyword_match'],
        'groups' => $evaluation['groups'],
    ]);
})->name('dashboard.resumes.show');

Route::get('/testdb', function () {
    $groups = Group::all();
    $users = User::all();
    dd($groups, $users);
});

Route::get('/dashboard/admin', function () {

    return view('dashboard.admin.index', ['users' => User::all()]);
});
