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
})->name('dashboard.groups.store');

Route::get('/dashboard/groups/{id}', function ($id) {
    $groups = Group::all();
    $group = collect($groups)->firstWhere('id', $id);
    if ($group == null) {
        dd($group);
    }

    $job_listings = $group->assignments;

    return view('dashboard.groups.show', [
        'job_listings' => $job_listings,
        'group' => $group
    ]);
}
)->name('dashboard.groups.show');

Route::get('/dashboard/groups/{id}/assignments/create', function ($id) {
    $groups = Group::all();
    $group = collect($groups)->firstWhere('id', $id);

    if ($group == null) {
        dd([$group, 'The Group data is null']);
    }

    return view('dashboard.groups.assignment-create', [
        'group' => $group,
    ]);
})->name('dashboard.groups.assignments.create');

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
