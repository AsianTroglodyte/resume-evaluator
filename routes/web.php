<?php

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

Route::get('/dashboard/resumes', function () {
    return view('dashboard.resumes');
});

Route::get('/dashboard/groups', function () {
    return view('dashboard.groups');
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

    return view('dashboard.resume', [
        'title' => $evaluation['name'],
        'evaluation' => $evaluation['ats_friendliness'],
        'keyword_match' => $evaluation['keyword_match'],
        'groups' => $evaluation['groups'],
    ]);
})->name('dashboard.resumes.show');

// Route::get('/contact', function() {
//     return view('contact');
// });
